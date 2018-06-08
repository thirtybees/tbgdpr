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

namespace TbGdprModule\Exception;

use TbGdprRequest;
use TbObjectRequest;
use Throwable;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AlreadyRequestedException
 *
 * @package TbGdprModule\Exception
 */
class AlreadyRequestedException extends GdprException
{
    /** @var TbObjectRequest|TbGdprRequest $request */
    private $request;

    /**
     * AlreadyRequestedException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     * @param                $request
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $request)
    {
        parent::__construct($message, $code, $previous);
        $this->request = $request;
    }

    /**
     * Get request
     *
     * @return TbGdprRequest|TbObjectRequest
     *
     * @since 1.0.0
     */
    public function getRequest()
    {
        return $this->request;
    }
}
