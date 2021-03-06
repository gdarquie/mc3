<?php

namespace App\Component\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Component\Error\Mc3Error;
use App\Component\Importer\ImporterVoter;
use App\Entity\Heredity\AbstractImportable;
use App\Entity\Import;
use App\Entity\Indexation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportDataPersister implements ContextAwareDataPersisterInterface
{
    private HttpClientInterface $client;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->em = $em;
    }

    /**
     * @param $data
     * @param array $context
     * @return bool
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Import;
    }

    /**
     * @param $data
     * @param array $context
     * @return object|void
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function persist($data, array $context = [])
    {
        $this->launchImport($data);
    }

    public function remove($data, array $context = [])
    {
        // no delete function
    }

    /**
     * @param Import $data
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function launchImport(Import $data):void
    {
        // check if last import and last indexation are finished
        if (!ImporterVoter::isAllowed($this->em)) {
            throw new Mc3Error('Not allowed to launched the import or indexation process');
        }

        $this->em->persist($data);
        $this->em->flush();

        try {
            $url = $_ENV['IMPORTER_API_URL'].'/import/all';
            $this->client->request(
                'POST',
                $url,
                [
                    'headers' => [
                        'mc3-importer-security-hash' => $_ENV['MC3_IMPORTER_SECURITY_KEY']
                    ],
                    'timeout' => 900
                ]
            );
        } catch(\Exception $e) {
            $this->updateImport($data, AbstractImportable::FAILED_STATUS);
            throw new Mc3Error('Forbidden access to Importer, it might be a problem with request header key :  '.$e->getMessage(), 400 );
        }
    }

    /**
     * @param Import $data
     * @param string $status
     * @param false $progress
     */
    private function updateImport(Import $data, string $status, $progress = false):void
    {
        $data->setStatus($status);
        $data->setInprogress($progress);
        $data->setUpdatedAt(new \DateTime());
        $this->em->persist($data);
        $this->em->flush();
    }

}