<?php

namespace App\Logging;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class LokiHandler extends AbstractProcessingHandler
{
    protected Client $client;

    protected string $url;

    protected array $defaultLabels;

    public function __construct(array $config)
    {
        parent::__construct($config['level'] ?? 'debug', true);

        $this->url = ($config['url'] ?? 'http://loki:3100').'/loki/api/v1/push';
        $this->defaultLabels = $config['labels'] ?? [];

        $this->client = new Client([
            'timeout' => 5,
            'connect_timeout' => 2,
        ]);
    }

    protected function write(LogRecord $record): void
    {
        // error_log('LokiHandler: write method called for level '.$record->level->getName());
        // error_log('LokiHandler: Raw message: '.$record->message);

        // Labels for the log stream (low cardinality, fixed values)
        $labels = array_merge($this->defaultLabels, [
            'level' => strtolower($record->level->getName()), // Use lowercase for Loki label convention
            'channel' => $record->channel,
        ]);

        $logLineData = [
            'message' => $record->message,
            'datetime' => $record->datetime->format('c'), // ISO 8601 timestamp
            'level_name' => $record->level->getName(), // Original level name
            'channel' => $record->channel, // Monolog channel
            // Merge context and extra for all dynamic fields
            'context' => array_merge($record->context, $record->extra),
        ];

        // Construct the log line including context
        $logLine = json_encode($logLineData);

        $nanoseconds = $record->datetime->getTimestamp() * 1_000_000_000;

        $entry = [
            'streams' => [
                [
                    'stream' => $labels, // Labels remain small and static
                    'values' => [
                        [
                            (string) $nanoseconds,
                            $logLine, // Full message with context is here
                        ],
                    ],
                ],
            ],
        ];

        // error_log('LokiHandler: Sending payload (truncated for log output): '.substr(json_encode($entry), 0, 500).'...');
        // error_log('LokiHandler: Full payload size: '.strlen(json_encode($entry)).' bytes');

        try {
            $response = $this->client->post($this->url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $entry,
            ]);

            // error_log('LokiHandler: Received response status: '.$response->getStatusCode());

        } catch (RequestException $e) {
            error_log('🚨 Loki push failed (RequestException): '.$e->getMessage());
            if ($e->hasResponse()) {
                error_log('🔍 Loki response: '.$e->getResponse()->getBody()->getContents());
            }
        } catch (\Exception $e) {
            error_log('🚨 Unknown error during Loki push (Generic Exception): '.$e->getMessage());
        }
    }

    // Override parent's formatHandlers so it doesn't try to JSON encode again
    // This allows us to control the full message string.
    protected function getDefaultFormatter(): \Monolog\Formatter\FormatterInterface
    {
        return new \Monolog\Formatter\JsonFormatter;
    }
}
