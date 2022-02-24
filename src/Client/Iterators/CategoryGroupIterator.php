<?php

namespace Otto\Market\Client\Iterators;

use Otto\Market\Client\Oauth2\Oauth2ApiAccessor;
use Otto\Market\Products\ObjectSerializer;
use Otto\Market\Products\Model\CategoryGroups;
use Otto\Market\Products\Model\CategoryGroup;
use Psr\Log\LoggerInterface;

class CategoryGroupIterator implements \Iterator
{
    private Oauth2ApiAccessor $accessor;

    private string $apiVersion;

    private string $productsPath;

    protected int $position = 0;

    protected int $page = 0;

    protected int $pageSize = 100;

    protected $nextLink;

    protected CategoryGroups $categoryGroups;

    private LoggerInterface $logger;

    public function __construct(
        Oauth2ApiAccessor $accessor,
        string $apiVersion,
        string $productsPath,
        LoggerInterface $logger,
        int $pageSize = 100
    ) {
        $this -> accessor     = $accessor;
        $this -> apiVersion   = $apiVersion;
        $this -> productsPath = $productsPath;
        $this -> pageSize     = max(10, min($pageSize, 1000));
        $this -> logger       = $logger;
    }

    public function rewind(): void
    {
        $pathParts = [$this -> apiVersion, $this -> productsPath, 'categories'];
        $this -> nextLink = implode("/", $pathParts) . "?" . "limit=" . strval($this -> pageSize);
        $this -> page     = -1;
        $this -> readCategoryGroups();
    }

    private function readCategoryGroups(): void
    {
        if (isset($this -> nextLink) && !is_null($this -> nextLink)) {
            $response = $this -> accessor -> get($this -> nextLink);
            $this -> categoryGroups = ObjectSerializer::deserialize(
                $response -> getBody() -> getContents(),
                '\Otto\Market\Products\Model\CategoryGroups'
            );
        } else {
            unset($this -> categoryGroups);
        }

        $this -> nextLink = null;
        if (isset($this -> categoryGroups) && !is_null($this -> categoryGroups)) {
            foreach ($this -> categoryGroups -> getLinks() as $link) {
                if ($link->getRel() == 'next') {
                    $this -> nextLink = $link->getHref();
                    $this -> logger -> debug("Accessing next link " . $link->getHref());
                    break;
                }
            }
        }

        ++$this -> page;
        $this -> logger -> debug("read page " . $this -> page);
        $this -> position = 0;
    }

    public function current(): CategoryGroup
    {
        return $this -> categoryGroups -> getCategoryGroups()[$this -> position];
    }

    public function key(): int
    {
        return ($this -> position + ($this -> page * $this -> pageSize));
    }

    public function next(): void
    {
        ++$this -> position;
        if ($this -> position >= $this -> pageSize) {
            $this -> readCategoryGroups();
        }
    }

    public function valid(): bool
    {
        return isset($this -> categoryGroups) &&
                isset($this -> categoryGroups -> getCategoryGroups()[$this -> position]);
    }
}
