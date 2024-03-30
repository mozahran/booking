<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait HasAuthenticatedClient
{
    protected function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient(
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
        );
        $credentials = json_encode([
            'username' => UserFixtures::REF_01_USERNAME,
            'password' => UserFixtures::REF_01_PASSWORD,
        ]);
        $client->request(
            method: 'POST',
            uri: 'v1/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $credentials,
        );
        $data = json_decode(
            json: $client->getResponse()->getContent(),
            associative: true,
        );

        if (isset($data['token'])) {
            $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
        }

        return $client;
    }
}
