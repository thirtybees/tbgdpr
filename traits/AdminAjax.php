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

namespace TbGdprModule;

use Db;
use DbQuery;
use Exception;
use Logger;
use PrestaShopException;
use TbGdprRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait AdminAjax
 *
 * @package TbGdprModule
 */
trait AdminAjax
{
    /**
     * @throws \Adapter_Exception
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     *
     * @since 1.0.0
     */
    public function ajaxProcessApproveRemovalRequest()
    {
        $input = @json_decode(file_get_contents('php://input'), true);
        if (!isset($input['idRequest'])) {
            die(json_encode([
                'success' => false,
                'message' => $this->l('Request not found', 'AdminAjax'),
            ]));
        }

        $request = new TbGdprRequest($input['idRequest']);
        if ($request->approve()) {
            try {
                $request->execute();
            } catch (PrestaShopException $e) {
                die(json_encode([
                    'success' => false,
                    'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
                ]));
            }

            die(json_encode([
                'success' => true,
                'message' => $this->l('Request successfully updated', 'AdminAjax'),
            ]));
        }

        die(json_encode([
            'success' => false,
            'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
        ]));
    }

    /**
     * @throws \Adapter_Exception
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     *
     * @since 1.0.0
     */
    public function ajaxProcessDenyRemovalRequest()
    {
        $input = @json_decode(file_get_contents('php://input'), true);
        if (!isset($input['idRequest'])) {
            die(json_encode([
                'success' => false,
                'message' => $this->l('Request not found', 'AdminAjax'),
            ]));
        }

        $request = new TbGdprRequest($input['idRequest']);
        if ($request->deny()) {
            try {
                $request->execute();
            } catch (PrestaShopException $e) {
                die(json_encode([
                    'success' => false,
                    'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
                ]));
            }

            die(json_encode([
                'success' => true,
                'message' => $this->l('Request successfully updated', 'AdminAjax'),
            ]));
        }

        die(json_encode([
            'success' => false,
            'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
        ]));
    }

    /**
     * Retrieve customer requests
     */
    public function ajaxProcessRetrieveCustomerRequests()
    {
        $input = dot(@json_decode(file_get_contents('php://input'), true));
        if ($input->has('type')) {
            die(json_encode([
                'success' => false,
                'message' => $this->l('Type not found', 'AdminAjax'),
            ]));
        }
        $page = $input->get('page', 0);
        $pageSize = $input->get('pageSize', static::DEFAULT_PAGINATION_LIMIT);
        $sorted = $input->get('sorted', []);
        $filtered = $input->get('filtered', []);

        $query = (new DbQuery())
            ->from(bqSQL(TbGdprRequest::$definition['table']))
        ;
        foreach ($filtered as $filter) {
            if (is_string($filter['value'])) {
                $query->where('`'.bqSQL($filter['id']).'` = \''.pSQL($filter['value']).'\'');
            } elseif (!empty($filter['value']['value'])) {
                switch ($filter['value']['type']) {
                    case 'emailhex':
                        $query->where('LEFT(HEX(`'.bqSQL($filter['id']).'`), 12) LIKE \'%'.pSQL($filter['value']['value']).'%\'');
                        break;
                    case 'bool':
                        if (in_array($filter['value']['value'], ['true', 'false'])) {
                            $query->where('`'.bqSQL($filter['id']).'` = '.($filter['value']['value'] === 'true' ? '1' : '0'));
                        }
                        break;
                    case 'string':
                        $query->where('`'.bqSQL($filter['id']).'` LIKE \'%'.pSQL($filter['value']['value']).'%\'');
                        break;
                    default:
                        $query->where('`'.bqSQL($filter['id']).'` = \''.pSQL($filter['value']['value']).'\'');
                        break;
                }
            }
        }

        $countQuery = clone $query;
        $countQuery->select('COUNT(*)');
        $query->select('`id_tbgdpr_request`, LEFT(HEX(`email`), 12) AS `email`, `date_add`, `date_upd`, `status`, `executed`, `comment`');
        $query->limit($pageSize, $page * $pageSize);
        if (!empty($sorted)) {
            $orderBy = [];
            foreach ($sorted as $sort) {
                $orderBy[] = '`'.bqSQL($sort['id']).'` '.($sort['desc'] ? 'DESC' : 'ASC');
            }
            $query->orderBy(implode(',', $orderBy));
        }
        try {
            $data = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            $count = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($countQuery);
        } catch (Exception $e) {
            Logger::addLog("TBGdpr Module error: {$e->getMessage()}");
            $data = [];
            $count = 0;
        }
        $pages = ceil($count / $pageSize);

        @ob_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success'  => true,
            'data'     => $data,
            'pages'    => (int) $pages,
            'rowCount' => (int) $count,
        ]);
        exit;
    }
}
