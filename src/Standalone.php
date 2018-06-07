<?php

/*
 * Bear CMS standalone
 * https://github.com/bearcms/standalone
 * Copyright (c) 2018 Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace BearCMS;

class Standalone
{

    /**
     *
     * @var array 
     */
    private $config = [];

    /**
     * 
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 
     * @return void
     */
    public function run(): void
    {
        $app = new \BearFramework\App();

        $config = $this->config;

        if (!isset($config['dataDir'])) {
            throw new \Exception('The dataDir option is required!');
        }

        if (!isset($config['logsDir'])) {
            throw new \Exception('The logsDir option is required!');
        }

        if (!isset($config['appSecretKey'])) {
            throw new \Exception('The appSecretKey option is required!');
        }

        $app->config->dataDir = $config['dataDir'];
        $app->config->logsDir = $config['logsDir'];
        $app->config->logErrors = isset($config['logErrors']) ? $config['logErrors'] : true;
        $app->config->displayErrors = isset($config['displayErrors']) ? $config['displayErrors'] : false;

        $app->initialize();

        $app->addons->add('bearcms/bearframework-addon', [
            'serverUrl' => 'https://r05.bearcms.com/',
            'appSecretKey' => $config['appSecretKey'],
            'logServerRequests' => false,
            'logServerRequestsData' => false,
            'features' => ['ELEMENTS', 'PAGES', 'BLOG', 'THEMES', 'COMMENTS', 'FORUMS', 'SETTINGS', 'NOTIFICATIONS', 'USERS'],
            'addDefaultThemes' => true,
            'defaultThemeID' => isset($config['defaultThemeID']) ? $config['defaultThemeID'] : 'bearcms/universal',
            'maxUploadsSize' => null,
            'useDataCache' => true,
            'dataCachePrefix' => md5($config['appSecretKey']),
            'htmlSandboxUrl' => 'https://cdn8.amcn.in/htmlSandbox.min.html',
            'uiColor' => isset($config['uiColor']) ? $config['uiColor'] : '#058cc4',
            'whitelabel' => isset($config['whitelabel']) ? $config['whitelabel'] : false,
        ]);

        $getHashedAppSecretKey = function() use ($config) {
            $parts = explode('-', $config['appSecretKey'], 2);
            if (sizeof($parts) === 2) {
                return strtoupper('sha256-' . $parts[0] . '-' . hash('sha256', $parts[1]));
            }
            return '';
        };

        $app->routes
                ->add('/-bearcms-standalone-server-call', function() use ($app, $getHashedAppSecretKey) {
                    if ($app->request->formData->getValue('appSecretKey') !== $getHashedAppSecretKey()) {
                        return;
                    }
                    $action = $app->request->formData->getValue('action');
                    if ($action === 'runTasks') {
                        $app->tasks->run();
                        $data = ['status' => 'ok'];
                    } elseif ($action === 'status') {
                        $data = ['status' => 'ok'];
                    } else {
                        $data = ['status' => 'error', 'message' => 'Unknown action (' . $action . ')'];
                    }
                    return new \BearFramework\App\Response\JSON($data);
                }, ['POST']);

        $app->run();
    }

}
