<?php

namespace Snippetify\SnippetSniffer\Common;

use Monolog\Logger as BaseLogger;
use Monolog\Handler\StreamHandler;

/**
 * Logger.
 */
final class Logger
{
    public const DEBUG = 100;
    public const INFO = 200;
    public const NOTICE = 250;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    public const ALERT = 550;
    public const EMERGENCY = 600;
    public const NAME = 'Snippetify';
    public const LOG_FILE = 'snippetify.log';

    /**
     * The config.
     *
     * @var string
     */
    private $config;

    /**
     * Logger.
     * @var Logger
     */
    private $logger;

    /**
     * Singletion.
     *
     * @var self
     */
    private static $instance;

    /**
     * Create new instance.
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        
        if (empty($config['name'])) $this->config['name'] = self::NAME;
        if (empty($config['file'])) $this->config['file'] = dirname(__FILE__) . '/../../logs/'.self::LOG_FILE;

        $this->logger = new BaseLogger($this->config['name']);
    }

    /**
     * Create an instance.
     *
     * @param  array  $config
     * @return  self
     */
    public static function create(array $config = []): self
    {
        if (is_null(self::$instance)) self::$instance = new self($config);

        return self::$instance;
    }


    /**
     * Log.
     *
     * @return void
     */
    public function log($message, $type = self::DEBUG): void {
        
        $this->logger->pushHandler(new StreamHandler($this->config['file'], self::DEBUG));

        switch ($type) {
            case self::ERROR:
                $this->logger->error($message);
            break;
            default:
                $this->logger->debug($message);
            break;
        }
    }
}
