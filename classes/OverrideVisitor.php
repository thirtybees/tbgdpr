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

use TbGdprModule\PhpParser\Comment;
use TbGdprModule\PhpParser\Node;
use TbGdprModule\PhpParser\Node\Stmt\Class_;
use TbGdprModule\PhpParser\Node\Stmt\ClassMethod;
use TbGdprModule\PhpParser\NodeTraverser;
use TbGdprModule\PhpParser\ParserFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OverrideVisitor
 *
 * @package TbGdprModule
 */
class OverrideVisitor implements \TbGdprModule\PhpParser\NodeVisitor
{
    /** @var string $classToTouch */
    protected $classToTouch = null;
    /** @var string $methodToInstall */
    protected $methodToInstall = null;
    protected $methodExists = false;
    /** @var string $methodToUninstall */
    protected $methodToUninstall = null;
    /** @var \TbGdpr $module */
    protected $module;
    /** @var array $overrides Override AST */
    protected static $overrides = [
        'FrontController::__destruct' => 'Override\\FrontControllerOverride',
    ];

    /**
     * OverrideVisitor constructor.
     *
     * @param $module
     *
     * @throws \PrestaShopException
     */
    public function __construct($module)
    {
        if (!$module->name || !$module->version) {
            throw new \PrestaShopException('Installing override for non-instantiated module');
        }

        $this->module = $module;
    }

    /**
     * @param string $methodToUninstall
     *
     * @return OverrideVisitor
     */
    public function setOverrideToUninstall($methodToUninstall)
    {
        $this->reset();

        list ($class, $method) = explode('::', $methodToUninstall);

        $this->classToTouch = $class;
        $this->methodToUninstall = $method;

        return $this;
    }

    /**
     * @param string $methodToInstall
     *
     * @return OverrideVisitor
     */
    public function setOverrideToInstall($methodToInstall)
    {
        $this->reset();

        list ($class, $method) = explode('::', $methodToInstall);

        $this->classToTouch = $class;
        $this->methodToInstall = $method;

        return $this;
    }

    /**
     * Reset remove/install settings
     */
    public function reset()
    {
        $this->methodToUninstall = null;
        $this->methodToInstall = null;
        $this->classToTouch = null;
    }

    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param \TbGdprModule\PhpParser\Node $nodes Array of nodes
     *
     * @return null|\TbGdprModule\PhpParser\Node Array of nodes
     */
    public function beforeTraverse(array $nodes)
    {
        return $nodes;
    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::DONT_TRAVERSE_CHILDREN
     *        => Children of $node are not traversed. $node stays as-is
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * otherwise
     *        => $node is set to the return value
     *
     * @param \TbGdprModule\PhpParser\Node $node Node
     *
     * @return null|int|\TbGdprModule\PhpParser\Node Node
     * @throws \PrestaShopException
     */
    public function enterNode(\TbGdprModule\PhpParser\Node $node)
    {
        if ($this->methodToInstall) {
            $overrideSource = realpath(str_replace('\\', '/', __DIR__.'/'.static::$overrides["{$this->classToTouch}::{$this->methodToInstall}"].'.php'));
            if (!$overrideSource) {
                throw new \PrestaShopException('Unable to install override');
            }
            if ($node instanceof Class_) {
                $this->methodExists = $node->getMethod($this->methodToInstall) instanceof ClassMethod;
                if (!$this->methodExists) {
                    $overrideSource = realpath(str_replace('\\', '/', __DIR__.'/'.static::$overrides["{$this->classToTouch}::{$this->methodToInstall}"].'.php'));
                    if (!$overrideSource) {
                        throw new \PrestaShopException('Unable to install override');
                    }
                    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
                    $stmts = $parser->parse(file_get_contents($overrideSource));
                    $newNode = null;
                    foreach ($stmts[0]->stmts as $stmt) {
                        if ($stmt instanceof Class_) {
                            $newNode = $stmt->getMethod($this->methodToInstall);
                            break;
                        }
                    }
                    if (!$newNode) {
                        throw new \PrestaShopException('Unable to install override');
                    }
                    $newNode->setAttribute('comments', [
                        new Comment(implode("\n", [
                            '/*',
                            "* module: {$this->module->name}",
                            '* date: '.date('Y-m-d H:i:s'),
                            "* version: {$this->module->version}",
                            '*/',
                        ])),
                    ]);

                    $node->stmts[] = $newNode;
                }
            } elseif ($node instanceof ClassMethod && $this->methodExists && $node->name === $this->methodToInstall) {
                // Only update if owned by the same module
                if ($this->getModuleName($node) === $this->module->name) {
                    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
                    $stmts = $parser->parse(file_get_contents($overrideSource));
                    $newNode = null;
                    foreach ($stmts[0]->stmts as $stmt) {
                        if ($stmt instanceof Class_) {
                            $newNode = $stmt->getMethod($this->methodToInstall);
                            break;
                        }
                    }
                    if (!$newNode) {
                        return $node;
                    }
                    $newNode->setAttribute('comments', [
                        new Comment(implode("\n", [
                            '/*',
                            "* module: {$this->module->name}",
                            '* date: '.date('Y-m-d H:i:s'),
                            "* version: {$this->module->version}",
                            '*/',
                        ])),
                    ]);
                } else {
                    throw new \PrestaShopException('Module override already claimed');
                }
            }
        }

        return $node;
    }

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::REMOVE_NODE
     *        => $node is removed from the parent array
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * array (of Nodes)
     *        => The return value is merged into the parent array (at the position of the $node)
     *  * otherwise
     *        => $node is set to the return value
     *
     * @param \TbGdprModule\PhpParser\Node $node Node
     *
     * @return null|false|int|\TbGdprModule\PhpParser\Node|\ThirtyBeesOverrideCheck\PhpParser\Node Node
     */
    public function leaveNode(\TbGdprModule\PhpParser\Node $node)
    {
        if ($node instanceof ClassMethod && $node->name === $this->methodToUninstall) {
            return NodeTraverser::REMOVE_NODE;
        }

        return $node;
    }

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param \TbGdprModule\PhpParser\Node $nodes Array of nodes
     *
     * @return null|\TbGdprModule\PhpParser\Node Array of nodes
     */
    public function afterTraverse(array $nodes)
    {
        $this->reset();

        return $nodes;
    }

    /**
     * Gets the normal comment (single asterisk) of the node.
     *
     * The doc comment has to be the last comment associated with the node.
     *
     * @param Node $node
     *
     * @return null|Comment\Doc Doc comment object or null
     */
    protected function getComment($node)
    {
        $comments = $node->getAttribute('comments');
        if (!$comments) {
            return null;
        }
        $lastComment = $comments[count($comments) - 1];
        if (!$lastComment instanceof Comment) {
            return null;
        }
        return $lastComment;
    }

    /**
     * @param Node $node
     *
     * @return null
     */
    protected function getModuleName($node)
    {
        $comment = $this->getComment($node);
        if ($comment instanceof Comment) {
             $parts = explode("\n", $comment->getText());
             foreach ($parts as $part) {
                 list($key, $value) = array_pad(array_map('trim', explode(':', $part)), 2, null);
                 if (strpos($key, 'module') !== false) {
                     return $value;
                 }
             }
        }

        return null;
    }

    /**
     * @param Node $node
     *
     * @return null
     */
    protected function getModuleVersion($node)
    {
        $comment = $this->getComment($node);
        if ($comment instanceof Comment) {
            $parts = explode("\n", $comment->getText());
            foreach ($parts as $part) {
                list($key, $value) = array_pad(array_map('trim', explode(':', $part)), 2, null);
                if (strpos($key, 'version') !== false) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @param Node $node
     *
     * @return null
     */
    protected function getModuleDate($node)
    {
        $comment = $this->getComment($node);
        if ($comment instanceof Comment) {
            $parts = explode("\n", $comment->getText());
            foreach ($parts as $part) {
                list($key, $value) = array_pad(array_map('trim', explode(':', $part)), 2, null);
                if (strpos($key, 'date') !== false) {
                    return $value;
                }
            }
        }

        return null;
    }
}
