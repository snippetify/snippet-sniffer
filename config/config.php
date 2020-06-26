<?php

return [

	/**
	* App 
	*/
	"app" => [
		"name" => "Snippet sniffer",
		"type" => "snippetify-sniffer",
		"version" => "1.0.0",
		"user_agent" => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:65.0) Gecko/20100101 Firefox/65.0',
	],

	/**
	* Search engine API provider
	*/
	"provider" => [
		"name" => "google",
		"cx" => "",
		"key" => "",
	],


	/**
	* Logger
	*/
	"logger" => [
		"enabled" => true,
		"name" => "Snippetify",
		"file" => dirname(__FILE__).'/../logs/snippetify.log'
	]
];