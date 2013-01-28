<?php

class DbaFrtNews
{

    private $memcache_key_footer = 'NoticiasElNuevoDia_Footer';
    private $memcache_key_tab = 'NoticiasElNuevoDia_Tab';

    public function __construct()
    {
	
    }



    public function getFooterNews()
    {
	return array();
	$content = ThefCache::get($this->memcache_key_footer);
	if (!$content) {
	    $content = file_get_contents(ThefConfig::get('NoticiasApiURL'));
	    ThefCache::set($this->memcache_key_footer, $content, ThefConfig::get('NoticiasTTL'));
	}
	$content = json_decode($content);
	if ($content->status == 'ok') {
	    $content = array_slice($content->posts, count($content->posts) - 20, count($content->posts));
	}
	return $content;
    }



    public function getTabNews()
    {
	return array();
	$content = ThefCache::get($this->memcache_key_tab);
	if (!$content) {
	    $content = file_get_contents(ThefConfig::get('NoticiasTabApiURL'));
	    ThefCache::set($this->memcache_key_tab, $content, ThefConfig::get('NoticiasTabTTL'));
	}
	$content = json_decode($content);
	if ($content->status == 'ok') {
	    $content = array_slice($content->posts, count($content->posts) - 20, count($content->posts));
	}
	return $content;
    }



}
