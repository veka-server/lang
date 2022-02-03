<?php

namespace VekaServer\Lang;

class Lang implements \VekaServer\Interfaces\LangInterface
{

    private $lang;
    private $bdd;

    public function __construct($lang, \VekaServer\Interfaces\BddInterface $bdd){
        $this->lang = $lang;
        $this->bdd = $bdd;
    }

    public function get($key){

        $sql = 'SELECT TV.trad 
                FROM traduction__value TV
                INNER JOIN traduction__lang TL ON TL.id_traduction_lang = TV.id_traduction_lang
                INNER JOIN traduction__key TK ON TK.id_traduction_key = TV.id_traduction_key
                WHERE LOWER(TK.uniq_key) = :uniq_key AND LOWER(TL.lang) = :lang ';
        $rs = $this->bdd->exec($sql, [
            's-uniq_key' => mb_strtolower($key)
            ,'s-lang' => mb_strtolower($this->lang)
        ]);

        if(empty($rs)) {
            return $key.'::TRAD_NOT_FOUND';
        }

        return $rs[0]['trad'];
    }

    public function has($key): bool
    {
        $sql = 'SELECT TV.trad 
                FROM traduction__value TV
                INNER JOIN traduction__lang TL ON TL.id_traduction_lang = TV.id_traduction_lang
                INNER JOIN traduction__key TK ON TK.id_traduction_key = TV.id_traduction_key
                WHERE LOWER(TK.uniq_key) = :uniq_key AND LOWER(TL.lang) = :lang ';
        $rs = $this->bdd->exec($sql, [
            's-uniq_key' => mb_strtolower($key)
            ,'s-lang' => mb_strtolower($this->lang)
        ]);

        return !empty($rs);
    }

    public function set($key, $lang, $traduction){
        $sql = 'INSERT INTO traduction__key (uniq_key) VALUES (:uniq_key) ON DUPLICATE KEY UPDATE id_traduction_key = id_traduction_key';
        $this->bdd->exec($sql, ['s-uniq_key' => mb_strtolower($key)]);

        $sql = 'SELECT id_traduction_lang , id_traduction_key
                FROM traduction__lang 
                INNER JOIN traduction__key TK ON TK.uniq_key = :uniq_key
                WHERE lang = :lang';
        $rs = $this->bdd->exec($sql, [ 's-lang' => mb_strtolower($lang) , 's-uniq_key' => mb_strtolower($key)]);
        $id_traduction_lang = $rs[0]['id_traduction_lang'];
        $id_traduction_key = $rs[0]['id_traduction_key'];

        $sql = 'INSERT INTO traduction__value (id_traduction_key, id_traduction_lang, trad) 
                VALUES (:id_traduction_key, :id_traduction_lang, :trad)';
        $this->bdd->exec($sql, [
            'i-id_traduction_key' => $id_traduction_key
            ,'i-id_traduction_lang' => $id_traduction_lang
            ,'s-trad' => mb_strtolower($traduction)
        ]);
    }

    public function addLang($lang){
        $sql = 'INSERT INTO traduction__lang (lang) VALUES (:lang) ON DUPLICATE KEY UPDATE id_traduction_lang = id_traduction_lang';
        $this->bdd->exec($sql, ['s-lang' => mb_strtolower($lang)]);
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

}
