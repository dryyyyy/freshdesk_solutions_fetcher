<?php

namespace App\Services;

/**
 * Class FreshdeskSDK
 * @package App\Services
 */
class FreshdeskSDK
{
    private $user;
    private $pw;
    private $url = 'https://movavi.freshdesk.com/api/v2/solutions/';

    private $categories = [];
    private $folders = [];
    private $raw_articles = [];
    private $allArticles = [];

    /**
     * FreshdeskSDK constructor.
     * @param string $user
     * @param string $password
     */
    public function __construct(string $user, string $password)
    {
        $this->user = $user;
        $this->pw = $password;
    }

    public function initCategories()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$this->url/categories");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pw");
        $result = curl_exec($ch);
        curl_close($ch);

        $rawCategoriesArray = json_decode($result, true);
        for ($i = 0; $i < count($rawCategoriesArray); $i++) {
            $this->categories[$rawCategoriesArray[$i]['name']] = $rawCategoriesArray[$i]['id'];
        }
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getCategoriesNames()
    {
        $names = [];
        foreach ($this->categories as $name => $id) {
            $names[] = $name;
        }
        return $names;
    }

    /**
     * @param $categoryId
     */
    public function initFolders($categoryId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$this->url/categories/$categoryId/folders");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pw");
        $result = curl_exec($ch);
        curl_close($ch);

        $this->folders = json_decode($result, true);
    }

    /**
     * @return array
     */
    public function getFoldersNames()
    {
        $names = [];
        foreach ($this->folders as $name => $id) {
            $names[] = $name;
        }
        return $names;
    }

    /**
     * @param $folderId
     * @param string $language_code
     */
    public function initArticles($folderId, string $language_code)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$this->url/folders/$folderId/articles/$language_code");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pw");
        $result = curl_exec($ch);
        curl_close($ch);

        $this->raw_articles = json_decode($result, true);
    }

    /**
     * @param $categoryId
     * @param string $language_code
     */
    public function fetchArticles($categoryId, string $language_code)
    {
        $this->initFolders($categoryId);

        $folderIds = [];
        $articles = [];
        $progressBar = new ProgressBar();

        foreach ($this->folders as $folder) {
            $folderIds[] = $folder['id'];
        }

        for ($i = 0; $i < count($folderIds); $i++) {
            $progressBar->advance();
            $this->initArticles($folderIds[$i], $language_code);
            $articles[] = $this->raw_articles;
        }

        for ($i = 0; $i < count($articles); $i++) {
            for ($j = 0; $j < count($articles[$i]); $j++) {
                $this->allArticles[$i][$j]['topic'] = $this->folders[$i]['name'];
                $this->allArticles[$i][$j]['query'] = $articles[$i][$j]['title'];
                $this->allArticles[$i][$j]['response'] = $articles[$i][$j]['description_text'];
                $this->allArticles[$i][$j]['html'] = $articles[$i][$j]['description'];
            }
        }
    }

    /**
     * @return array
     */
    public function getAllArticles()
    {
        return $this->allArticles;
    }
}