<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Adapter\Zend;

/**
 * This class adds the possibility to hide destructor errors
 * which generally occur when running consecutive tests.
 */
class MultiSearcher extends \Zend_Search_Lucene_MultiSearcher
{
public function find($query)
{
    if (count($this->_indices) == 0) {
        return array();
    }

    $hitsList = array();
    foreach ($this->_indices as $index) {
        $hits = $index->find($query);

        $hitsList = $this->merge($hits, $hitsList);
    }

    return $hitsList;
}

protected function merge(&$leftList, &$rightList)
{
    $results = array();
    while(0 < count($leftList) && 0 < count($rightList)) {
        if($leftList[0]->score < $rightList[0]->score) {
            $results[] = array_shift($leftList);
        } else {
            $results[] = array_shift($rightList);
        }
    }

    $results = count($leftList) > count($rightList) 
        ? array_merge($results, $leftList) : array_merge($results, $rightList);

    return $results;
}
}
