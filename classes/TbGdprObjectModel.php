<?php
/**
 * Copyright (C) 2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2018 thirty bees
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use TbGdprModule\Tools as GdprTools;

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class TbGdprObjectModel
 */
class TbGdprObjectModel extends ObjectModel
{
    const TYPE_HEX = 9;

    /**
     *  Create the database table with its columns. Similar to the createColumn() method.
     *
     * @param string|null $className Class name
     *
     * @return bool Indicates whether the database was successfully added
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function createDatabase($className = null)
    {
        $success = true;

        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = static::getDefinition($className);
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.bqSQL($definition['table']).'` (';
        $sql .= '`'.$definition['primary'].'` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,';
        foreach ($definition['fields'] as $fieldName => $field) {
            if ($fieldName === $definition['primary'] || (isset($field['lang']) && $field['lang'])) {
                continue;
            }
            $sql .= '`'.$fieldName.'` '.$field['db_type'];
            if (isset($field['required'])) {
                $sql .= ' NOT NULL';
            }
            if (isset($field['default'])) {
                $sql .= ' DEFAULT \''.$field['default'].'\'';
            }
            $sql .= ',';
        }
        $sql = trim($sql, ',');
        $sql .= ') ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci';

        try {
            $success &= \Db::getInstance()->execute($sql);
        } catch (\PrestaShopException $exception) {
            static::dropDatabase($className);

            return false;
        }

        if (isset($definition['multilang']) && $definition['multilang']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.bqSQL($definition['table']).'_lang` (';
            $sql .= '`'.$definition['primary'].'` INT(11) UNSIGNED NOT NULL,';
            foreach ($definition['fields'] as $fieldName => $field) {
                if ($fieldName === $definition['primary'] || !(isset($field['lang']) && $field['lang'])) {
                    continue;
                }
                $sql .= '`'.$fieldName.'` '.$field['db_type'];
                if (isset($field['required'])) {
                    $sql .= ' NOT NULL';
                }
                if (isset($field['default'])) {
                    $sql .= ' DEFAULT \''.$field['default'].'\'';
                }
                $sql .= ',';
            }

            // Lang field
            $sql .= '`id_lang` INT(11) DEFAULT NULL,';
            if (isset($definition['multilang_shop']) && $definition['multilang_shop']) {
                $sql .= '`id_shop` INT(11) DEFAULT NULL,';
            }

            // Primary key
            $sql .= 'PRIMARY KEY (`'.bqSQL($definition['primary']).'`, `id_lang`)';


            $sql .= ') ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci';

            try {
                $success &= \Db::getInstance()->execute($sql);
            } catch (\PrestaShopException $exception) {
                static::dropDatabase($className);

                return false;
            }
        }

        if (isset($definition['multishop']) && $definition['multishop']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.bqSQL($definition['table']).'_shop` (';
            $sql .= '`'.$definition['primary'].'` INT(11) UNSIGNED NOT NULL,';
            foreach ($definition['fields'] as $fieldName => $field) {
                if ($fieldName === $definition['primary'] || !(isset($field['shop']) && $field['shop'])) {
                    continue;
                }
                $sql .= '`'.$fieldName.'` '.$field['db_type'];
                if (isset($field['required'])) {
                    $sql .= ' NOT NULL';
                }
                if (isset($field['default'])) {
                    $sql .= ' DEFAULT \''.$field['default'].'\'';
                }
                $sql .= ',';
            }

            // Shop field
            $sql .= '`id_shop` INT(11) DEFAULT NULL,';

            // Primary key
            $sql .= 'PRIMARY KEY (`'.bqSQL($definition['primary']).'`, `id_shop`)';

            $sql .= ') ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci';

            try {
                $success &= \Db::getInstance()->execute($sql);
            } catch (\PrestaShopException $exception) {
                static::dropDatabase($className);

                return false;
            }
        }

        return $success;
    }

    /**
     * Drop the database for this ObjectModel
     *
     * @param string|null $className Class name
     *
     * @return bool Indicates whether the database was successfully dropped
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function dropDatabase($className = null)
    {
        $success = true;
        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = \ObjectModel::getDefinition($className);

        $success &= \Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_
            .bqSQL($definition['table']).'`');

        if (isset($definition['multilang']) && $definition['multilang']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $success &= \Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_
                .bqSQL($definition['table']).'_lang`');
        }

        if (isset($definition['multishop']) && $definition['multishop']
            || isset($definition['multilang_shop']) && $definition['multilang_shop']) {
            $success &= \Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_
                .bqSQL($definition['table']).'_shop`');
        }

        return $success;
    }

    /**
     * Get columns in database
     *
     * @param string|null $className Class name
     *
     * @return array|false|\mysqli_result|null|\PDOStatement|resource
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function getDatabaseColumns($className = null)
    {
        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = \ObjectModel::getDefinition($className);
        $sql = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=\''._DB_NAME_.'\' AND TABLE_NAME=\''
            ._DB_PREFIX_.pSQL($definition['table']).'\'';

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * Add a column in the table relative to the ObjectModel.
     * This method uses the $definition property of the ObjectModel,
     * with some extra properties.
     *
     * Example:
     * 'table'        => 'tablename',
     * 'primary'      => 'id',
     * 'fields'       => array(
     *     'id'     => array('type' => static::TYPE_INT, 'validate' => 'isInt'),
     *     'number' => array(
     *         'type'     => static::TYPE_STRING,
     *         'db_type'  => 'varchar(20)',
     *         'required' => true,
     *         'default'  => '25'
     *     ),
     * ),
     *
     * The primary column is date_add automatically as INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT. The other columns
     * require an extra parameter, with the type of the column in the database.
     *
     * @param string      $name             Column name
     * @param string      $columnDefinition Column type definition
     * @param string|null $className        Class name
     *
     * @return bool Indicates whether the column was successfully date_add
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function createColumn($name, $columnDefinition, $className = null)
    {
        if (empty($className)) {
            $className = get_called_class();
        }

        $definition = static::getDefinition($className);
        $sql = 'ALTER TABLE `'._DB_PREFIX_.bqSQL($definition['table']).'`';
        $sql .= ' ADD COLUMN `'.bqSQL($name).'` '.bqSQL($columnDefinition['db_type']).'';
        if ($name === $definition['primary']) {
            $sql .= ' INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT';
        } else {
            if (isset($columnDefinition['required']) && $columnDefinition['required']) {
                $sql .= ' NOT NULL';
            }
            if (isset($columnDefinition['default'])) {
                $sql .= ' DEFAULT "'.pSQL($columnDefinition['default']).'"';
            }
        }

        return (bool) \Db::getInstance()->execute($sql);
    }

    /**
     *  Create in the database every column detailed in the $definition property that are
     *  missing in the database.
     *
     * @param string|null $className Class name
     *
     * @return bool Indicates whether the missing columns were successfully date_add
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     * @todo: Support multishop and multilang
     */
    public static function createMissingColumns($className = null)
    {
        if (empty($className)) {
            $className = get_called_class();
        }

        $success = true;

        $definition = static::getDefinition($className);
        $columns = static::getDatabaseColumns();
        foreach ($definition['fields'] as $columnName => $columnDefinition) {
            //column exists in database
            $exists = false;
            foreach ($columns as $column) {
                if ($column['COLUMN_NAME'] === $columnName) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $success &= static::createColumn($columnName, $columnDefinition);
            }
        }

        return $success;
    }

    /**
     * Formats values of each fields.
     *
     * @param int $type   FORMAT_COMMON or FORMAT_LANG or FORMAT_SHOP
     * @param int $idLang If this parameter is given, only take lang fields
     *
     * @return array
     *
     * @since   1.0.0
     * @throws PrestaShopException
     * @throws HTMLPurifier_Exception
     */
    protected function formatFields($type, $idLang = null)
    {
        $fields = [];

        // Set primary key in fields
        if (isset($this->id)) {
            $fields[$this->def['primary']] = $this->id;
        }

        foreach ($this->def['fields'] as $field => $data) {
            // Only get fields we need for the type
            // E.g. if only lang fields are filtered, ignore fields without lang => true
            if (($type == static::FORMAT_LANG && empty($data['lang']))
                || ($type == static::FORMAT_SHOP && empty($data['shop']))
                || ($type == static::FORMAT_COMMON && ((!empty($data['shop']) && $data['shop'] != 'both') || !empty($data['lang'])))) {
                continue;
            }

            if (is_array($this->update_fields)) {
                if ((!empty($data['lang']) || (!empty($data['shop']) && $data['shop'] != 'both')) && (empty($this->update_fields[$field]) || ($type == static::FORMAT_LANG && empty($this->update_fields[$field][$idLang])))) {
                    continue;
                }
            }

            // Get field value, if value is multilang and field is empty, use value from default lang
            $value = $this->$field;
            if ($type == static::FORMAT_LANG && $idLang && is_array($value)) {
                if (!empty($value[$idLang])) {
                    $value = $value[$idLang];
                } elseif (!empty($data['required'])) {
                    $value = $value[Configuration::get('PS_LANG_DEFAULT')];
                } else {
                    $value = '';
                }
            }

            $purify = (isset($data['validate']) && mb_strtolower($data['validate']) == 'iscleanhtml') ? true : false;
            // Format field value
            $fields[$field] = static::formatValue($value, $data['type'], false, $purify, !empty($data['allow_null']));
        }

        return $fields;
    }

    /**
     * Formats a value
     *
     * @param mixed $value
     * @param int   $type
     * @param bool  $withQuotes
     * @param bool  $purify
     * @param bool  $allowNull
     *
     * @return mixed
     *
     * @since   1.0.0
     * @throws PrestaShopException
     * @throws HTMLPurifier_Exception
     */
    public static function formatValue($value, $type, $withQuotes = false, $purify = true, $allowNull = false)
    {
        if ($allowNull && $value === null) {
            return ['type' => 'sql', 'value' => 'NULL'];
        }

        switch ($type) {
            case self::TYPE_INT:
                return (int) $value;

            case self::TYPE_BOOL:
                return (int) $value;

            case self::TYPE_FLOAT:
                return (float) str_replace(',', '.', $value);

            case self::TYPE_DATE:
                if (!$value) {
                    return '0000-00-00';
                }

                if ($withQuotes) {
                    return '\''.pSQL($value).'\'';
                }
                return pSQL($value);

            case self::TYPE_HTML:
                if ($purify) {
                    $value = Tools::purifyHTML($value);
                }
                if ($withQuotes) {
                    return '\''.pSQL($value, true).'\'';
                }
                return pSQL($value, true);

            case self::TYPE_SQL:
                if ($withQuotes) {
                    return '\''.pSQL($value, true).'\'';
                }
                return pSQL($value, true);

            case self::TYPE_NOTHING:
                return $value;

            case self::TYPE_HEX:
                return ['type' => 'sql', 'value' => '0x'.GdprTools::sanitizeHex($value)];

            case self::TYPE_STRING:
            default :
                if ($withQuotes) {
                    return '\''.pSQL($value).'\'';
                }
                return pSQL($value);
        }
    }

    /**
     * Takes current object ID, gets its values from database,
     * saves them in a new row and loads newly saved values as a new object.
     *
     * @return ObjectModel|false
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     *
     * @since   1.0.0
     */
    public function duplicateObject()
    {
        $definition = ObjectModel::getDefinition($this);

        $res = Db::getInstance()->getRow('
					SELECT *
					FROM `'._DB_PREFIX_.bqSQL($definition['table']).'`
					WHERE `'.bqSQL($definition['primary']).'` = '.(int) $this->id
        );
        if (!$res) {
            return false;
        }

        unset($res[$definition['primary']]);
        foreach ($res as $field => &$value) {
            if (isset($definition['fields'][$field])) {
                $value = static::formatValue($value, $definition['fields'][$field]['type'], false, true, !empty($definition['fields'][$field]['allow_null']));
            }
        }

        if (!Db::getInstance()->insert($definition['table'], $res)) {
            return false;
        }

        $objectId = Db::getInstance()->Insert_ID();

        if (isset($definition['multilang']) && $definition['multilang']) {
            $result = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.bqSQL($definition['table']).'_lang`
			WHERE `'.bqSQL($definition['primary']).'` = '.(int) $this->id);
            if (!$result) {
                return false;
            }

            foreach ($result as &$row) {
                foreach ($row as $field => &$value) {
                    if (isset($definition['fields'][$field])) {
                        $value = static::formatValue($value, $definition['fields'][$field]['type'], false, true, !empty($definition['fields'][$field]['allow_null']));
                    }
                }
            }

            // Keep $row2, you cannot use $row because there is an unexplicated conflict with the previous usage of this variable
            foreach ($result as $row2) {
                $row2[$definition['primary']] = (int) $objectId;
                if (!Db::getInstance()->insert($definition['table'].'_lang', $row2)) {
                    return false;
                }
            }
        }

        /** @var ObjectModel $objectDuplicated */
        $objectDuplicated = new $definition['classname']((int) $objectId);
        $objectDuplicated->duplicateShops((int) $this->id);

        return $objectDuplicated;
    }
}
