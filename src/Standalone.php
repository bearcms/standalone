<?php

/*
 * Bear CMS standalone
 * https://github.com/bearcms/standalone
 * Copyright (c) Amplilabs Ltd.
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

        $app->enableErrorHandler([
            'logErrors' => isset($config['logErrors']) ? (int) $config['logErrors'] > 0 : true,
            'displayErrors' => isset($config['displayErrors']) ? (int) $config['displayErrors'] > 0 : false,
        ]);

        if (!isset($config['dataDir'])) {
            throw new \Exception('The dataDir option is required!');
        }

        if (!isset($config['logsDir'])) {
            throw new \Exception('The logsDir option is required!');
        }

        if (!isset($config['appSecretKey'])) {
            throw new \Exception('The appSecretKey option is required!');
        }

        $app->data->useFileDriver((string) $config['dataDir']);
        $app->cache->useAppDataDriver();
        if (isset($config['logsDir']) && strlen($config['logsDir']) > 0) {
            $app->logs->useFileLogger((string) $config['logsDir']);
        } else {
            $app->logs->useNullLogger();
        }

        $bearCMSConfig = [
            'serverUrl' => isset($config['serverUrl']) ? $config['serverUrl'] : 'https://r05.bearcms.com/',
            'appSecretKey' => $config['appSecretKey'],
            'addDefaultThemes' => true,
            'defaultThemeID' => isset($config['defaultThemeID']) ? $config['defaultThemeID'] : 'bearcms/themeone',
            'maxUploadsSize' => null,
            'htmlSandboxUrl' => 'https://cdn8.amcn.in/htmlSandbox.min.html',
            'uiColor' => isset($config['uiColor']) ? $config['uiColor'] : null,
            'uiTextColor' => isset($config['uiTextColor']) ? $config['uiTextColor'] : null,
            'whitelabel' => isset($config['whitelabel']) ? $config['whitelabel'] : false,
            'internalAppSpecificServerData' => [
                'clientID' => 'bearcms/standalone',
                'releaseChannel' => isset($config['releaseChannel']) ? $config['releaseChannel'] : ''
            ]
        ];
        if (isset($config['standalone-manager-filepath'])) {
            $bearCMSConfig['internalAddonManager'] = function () use ($config) {
                return include $config['standalone-manager-filepath'];
            };
            $bearCMSConfig['internalConfigManager'] = function () use ($config) {
                return include $config['standalone-manager-filepath'];
            };
        }
        $app->addons->add('bearcms/bearframework-addon');
        $app->bearCMS->initialize($bearCMSConfig);

        $getHashedAppSecretKey = function () use ($config) {
            $parts = explode('-', $config['appSecretKey'], 2);
            if (sizeof($parts) === 2) {
                return strtoupper('sha256-' . $parts[0] . '-' . hash('sha256', $parts[1]));
            }
            return '';
        };

        $app->routes
            ->add('POST /-bearcms-standalone-server-call', function () use ($app, $getHashedAppSecretKey) {
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
                return new \BearFramework\App\Response\JSON(json_encode($data));
            });

        $app->run();
    }
}
