<?php
require_once dirname(__FILE__).'/'.'romaji.php';

class RomajiAnagram extends Romaji {

    var $src = '';

    function __construct($src) {
        $this->src = str_replace(' ', '', trim($src));
    }

    function getAnagramList() {
        if ($tree = $this->tree()) {
            return $this->tree2List($tree);
        }
        return array();
    }

    function tree() {
        foreach (array('l','q','v','x') as $useless) {
            /* ローマ字に使用できない文字が含まれている場合は抜ける */
            if (strpos($this->src, $useless) !== FALSE) { return array(); }
        }
        return $this->treeRecursive($this->src);
    }

    function treeRecursive($src, $buildStr = '') {
        $len = strlen($src);
        /* 枝の終端   */
        if (!$len) { return NULL; }

        $srcMap = 0x00000000;
        for ($i = 0; $i < $len; $i++) {
            $srcMap |= $this->chrBits[$src[$i]];
        }
        /* 残りの文字列に母音 or n が存在しない場合は枝の生成を停止 */
        if (!($srcMap & $this->nBoinMap)) { return FALSE; }

        $tree = FALSE;
        $chrBitMap = 0x00000000;
        for ($i = 0; $i < $len; $i++) {
            $chr = $src[$i];
            /* 既に計算済みの枝はスキップする  */
            if ($chrBitMap & $this->chrBits[$chr]) { continue; }

            $chrBitMap |= $this->chrBits[$chr];
            $isRomaji = $this->isRomaji($buildStr.$chr);
            /* ローマ字として成立しない並びの場合スキップする  */
            if (($isRomaji === FALSE) || ($isRomaji === NULL && $len == 1)) {
                continue;
            }
            if (($child = $this->treeRecursive(substr_replace($src, '', $i, 1), $buildStr.$chr)) !== FALSE) {
                $tree[$chr] = $child;
            }
        }
        return $tree;
    }
}
