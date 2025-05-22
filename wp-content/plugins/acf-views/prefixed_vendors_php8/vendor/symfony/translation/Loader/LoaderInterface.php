<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Loader;

use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Exception\InvalidResourceException;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Exception\NotFoundResourceException;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\MessageCatalogue;
/**
 * LoaderInterface is the interface implemented by all translation loaders.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface LoaderInterface
{
    /**
     * Loads a locale.
     *
     * @throws NotFoundResourceException when the resource cannot be found
     * @throws InvalidResourceException  when the resource cannot be loaded
     */
    public function load(mixed $resource, string $locale, string $domain = 'messages') : MessageCatalogue;
}
