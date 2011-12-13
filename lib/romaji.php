<?php
class Romaji {

    var $chrBits = array('a'=>0x00000001,'b'=>0x00000002,'c'=>0x00000004,
                        'd'=>0x00000008,'e'=>0x00000010,'f'=>0x00000020,
                        'g'=>0x00000040,'h'=>0x00000080,'i'=>0x00000100,
                        'j'=>0x00000200,'k'=>0x00000400,'l'=>0x00000800,
                        'm'=>0x00001000,'n'=>0x00002000,'o'=>0x00004000,
                        'p'=>0x00008000,'q'=>0x00010000,'r'=>0x00020000,
                        's'=>0x00040000,'t'=>0x00080000,'u'=>0x00100000,
                        'v'=>0x00200000,'w'=>0x00400000,'x'=>0x00800000,
                        'y'=>0x01000000,'z'=>0x02000000
                        );
    var $boinMap = 0x00104111;   /* a,i,u,e,o */
    var $nBoinMap = 0x00106111;  /* a,i,u,e,o,n */
    var $prefixMap = 0x34eb6ee;  /* k,s,t,n,h,f,m,y,r,w,g,z,j,d,b,p,c */

    function __construct() {
    }

    /*
    * Romaji to Ja-Kana
    * @param String: Romaji string.
    * @return Array: Ja-Kana array.
    */
    function romaji2Kana($src) {
        if ($yomiTree = Romaji::buildKanaTree($src)) {
            return Romaji::tree2List($yomiTree);
        }
        return array();
    }

    /*
    * Convert Tree data to List data.
    * @param Array: Tree data
    * @return Array: List data
    */
    function tree2List($tree, $buildString = '') {
        if (!is_array($tree)) { return array(); }
        $list = array();
        foreach ($tree as $key => $child) {
            if (is_array($child)) {
                $list = array_merge($list, Romaji::tree2List($child, $buildString.$key));
            } else {
                $list[] = $buildString.$key;
            }
        }
        return $list;
    }

    /*
    * Romaji spell checker.
    * @param String: Romaji string.
    * @return TRUE:  Is Romaji spell.
    * @return FALSE: Not Romaji spell.
    * @return NULL:  Not Romaji spell(No tail).
    */
    function isRomaji($src) {
        if (!$src) { return FALSE; }
        while ($src) {
            $len = strlen($src);
            if (Romaji::validChar2Kana($src[0])) {
                $src = substr($src, 1);
            } else if ($len >= 2 && Romaji::validChar2Kana($src[0].$src[1])) {
                $src = substr($src, 2);
            } else if ($len >= 3 && Romaji::validChar2Kana($src[0].$src[1].$src[2])) {
                $src = substr($src, 3);
            } else {
                if ($len == 1 && ($this->prefixMap & $this->chrBits[$src])) {
                    return NULL;    /* No tail. */
                } else if ($len == 2 && strpos('ky sh ch ny hy my ry gy by py ts mb mm mp', $src) !== FALSE) {
                    return NULL;    /* No tail. */
                }
                return FALSE;
            }
        }
        return TRUE;
    }

    function buildKanaTree($src, $buildStr = '') {
        if (!$src) { return $buildStr; }
        $tree = FALSE;
        for ($i = 0; $i < 3; $i++) {
            if ($kana = Romaji::char2Kana(substr($src, 0, $i+1))) {
                if (($child = Romaji::buildKanaTree(substr($src, $i+1), $buildStr.$kana)) !== FALSE) {
                    $tree[$kana] = $child;
                }
            }
        }
        return $tree;
    }

    function validChar2Kana($chr) {
        switch (strlen($chr)) {
        case 1:
            if ($this->nBoinMap & $this->chrBits[$chr]) {
                return TRUE;
            }
            return FALSE;
        case 2:
            if (strpos('ka ki ku ke ko sa su se so ta te to na ni nu ne no ha hi fu he ho ma mi mu me mo ya yu yo ra ri ru re ro wa wo ga gi gu ge go za ji zu ze zo da de do ba bi bu be bo pa pi pu pe po ja ju jo', $chr) !== FALSE) {
                return TRUE;
            }
            return FALSE;
        case 3:
            if (strpos('kya kyu kyo sha shu sho cha chu cho nya nyu nyo hya hyu hyo mya myu myo rya ryu ryo gya gyu gyo bya byu byo pya pyu pyo shi chi tsu mba mbi mbu mbe mbo mma mmi mmu mme mmo mpa mpi mpu mpe mpo', $chr) !== FALSE) {
                return TRUE;
            }
            return FALSE;
        default:
            break;
        }
        return FALSE;
    }

    function char2Kana($chr) {
        switch (strlen($chr)) {
        case 1:
            $h1Char = array('a'=>'あ', 'i'=>'い', 'u'=>'う', 'e'=>'え', 'o'=>'お', 'n'=>'ん');
            return isset($h1Char[$chr]) ? $h1Char[$chr] : FALSE;
        case 2:
            $h2Char = array('ka'=>'か', 'ki'=>'き', 'ku'=>'く', 'ke'=>'け', 'ko'=>'こ'
                            ,'sa'=>'さ', 'su'=>'す', 'se'=>'せ', 'so'=>'そ'
                            ,'ta'=>'た', 'te'=>'て', 'to'=>'と'
                            ,'na'=>'な', 'ni'=>'に', 'nu'=>'ぬ', 'ne'=>'ね', 'no'=>'の'
                            ,'ha'=>'は', 'hi'=>'ひ', 'fu'=>'ふ', 'he'=>'へ', 'ho'=>'ほ'
                            ,'ma'=>'ま', 'mi'=>'み', 'mu'=>'む', 'me'=>'め', 'mo'=>'も'
                            ,'ya'=>'や', 'yu'=>'ゆ', 'yo'=>'よ'
                            ,'ra'=>'ら', 'ri'=>'り', 'ru'=>'る', 're'=>'れ', 'ro'=>'ろ'
                            ,'wa'=>'わ', 'wo'=>'を'
                            ,'ga'=>'が', 'gi'=>'ぎ', 'gu'=>'ぐ', 'ge'=>'げ', 'go'=>'ご'
                            ,'za'=>'ざ', 'ji'=>'じ', 'zu'=>'ず', 'ze'=>'ぜ', 'zo'=>'ぞ'
                            ,'da'=>'だ', 'de'=>'で', 'do'=>'ど'
                            ,'ba'=>'ば', 'bi'=>'び', 'bu'=>'ぶ', 'be'=>'べ', 'bo'=>'ぼ'
                            ,'pa'=>'ぱ', 'pi'=>'ぴ', 'pu'=>'ぷ', 'pe'=>'ぺ', 'po'=>'ぽ'
                            ,'ja'=>'じゃ', 'ju'=>'じゅ', 'jo'=>'じょ'
                            );
            return isset($h2Char[$chr]) ? $h2Char[$chr] : FALSE;
        case 3:
            $h3Char = array('kya'=>'きゃ', 'kyu'=>'きゅ', 'kyo'=>'きょ'
                            ,'sha'=>'しゃ', 'shu'=>'しゅ', 'sho'=>'しょ'
                            ,'cha'=>'ちゃ', 'chu'=>'ちゅ', 'cho'=>'ちょ'
                            ,'nya'=>'にゃ', 'nyu'=>'にゅ', 'nyo'=>'にょ'
                            ,'hya'=>'ひゃ', 'hyu'=>'ひゅ', 'hyo'=>'ひょ'
                            ,'mya'=>'みゃ', 'myu'=>'みゅ', 'myo'=>'みょ'
                            ,'rya'=>'りゃ', 'ryu'=>'りゅ', 'ryo'=>'りょ'
                            ,'gya'=>'ぎゃ', 'gyu'=>'ぎゅ', 'gyo'=>'ぎょ'
                            ,'bya'=>'びゃ', 'byu'=>'びゅ', 'byo'=>'びょ'
                            ,'pya'=>'ぴゃ', 'pyu'=>'ぴゅ', 'pyo'=>'ぴょ'
                            ,'shi'=>'し', 'chi'=>'ち', 'tsu'=>'つ'
                            ,'mba'=>'んば','mbi'=>'んび','mbu'=>'んぶ','mbe'=>'んべ','mbo'=>'んぼ'
                            ,'mma'=>'んま','mmi'=>'んみ','mmu'=>'んむ','mme'=>'んめ','mmo'=>'んも'
                            ,'mpa'=>'んぱ','mpi'=>'んぴ','mpu'=>'んぷ','mpe'=>'んぺ','mpo'=>'んぽ'
                            );
            return isset($h3Char[$chr]) ? $h3Char[$chr] : FALSE;
        default:
            break;
        }
        return FALSE;
    }
}
