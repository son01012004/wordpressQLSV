<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Test;

use Org\Wplake\Advanced_Views\Optional_Vendors\PHPUnit\Framework\MockObject\MockObject;
use Org\Wplake\Advanced_Views\Optional_Vendors\PHPUnit\Framework\TestCase;
use Org\Wplake\Advanced_Views\Optional_Vendors\Psr\Log\LoggerInterface;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\HttpClient\MockHttpClient;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Dumper\XliffFileDumper;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Exception\IncompleteDsnException;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Exception\UnsupportedSchemeException;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Loader\LoaderInterface;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Provider\Dsn;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\Provider\ProviderFactoryInterface;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Component\Translation\TranslatorBagInterface;
use Org\Wplake\Advanced_Views\Optional_Vendors\Symfony\Contracts\HttpClient\HttpClientInterface;
/**
 * A test case to ease testing a translation provider factory.
 *
 * @author Mathieu Santostefano <msantostefano@protonmail.com>
 */
abstract class ProviderFactoryTestCase extends TestCase
{
    protected HttpClientInterface $client;
    protected LoggerInterface|MockObject $logger;
    protected string $defaultLocale;
    protected LoaderInterface|MockObject $loader;
    protected XliffFileDumper|MockObject $xliffFileDumper;
    protected TranslatorBagInterface|MockObject $translatorBag;
    public abstract function createFactory() : ProviderFactoryInterface;
    /**
     * @return iterable<array{0: bool, 1: string}>
     */
    public static abstract function supportsProvider() : iterable;
    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public static abstract function createProvider() : iterable;
    /**
     * @return iterable<array{0: string, 1: string|null}>
     */
    public static function unsupportedSchemeProvider() : iterable
    {
        return [];
    }
    /**
     * @return iterable<array{0: string, 1: string|null}>
     */
    public static function incompleteDsnProvider() : iterable
    {
        return [];
    }
    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(bool $expected, string $dsn)
    {
        $factory = $this->createFactory();
        $this->assertSame($expected, $factory->supports(new Dsn($dsn)));
    }
    /**
     * @dataProvider createProvider
     */
    public function testCreate(string $expected, string $dsn)
    {
        $factory = $this->createFactory();
        $provider = $factory->create(new Dsn($dsn));
        $this->assertSame($expected, (string) $provider);
    }
    /**
     * @dataProvider unsupportedSchemeProvider
     */
    public function testUnsupportedSchemeException(string $dsn, ?string $message = null)
    {
        $factory = $this->createFactory();
        $dsn = new Dsn($dsn);
        $this->expectException(UnsupportedSchemeException::class);
        if (null !== $message) {
            $this->expectExceptionMessage($message);
        }
        $factory->create($dsn);
    }
    /**
     * @dataProvider incompleteDsnProvider
     */
    public function testIncompleteDsnException(string $dsn, ?string $message = null)
    {
        $factory = $this->createFactory();
        $dsn = new Dsn($dsn);
        $this->expectException(IncompleteDsnException::class);
        if (null !== $message) {
            $this->expectExceptionMessage($message);
        }
        $factory->create($dsn);
    }
    protected function getClient() : HttpClientInterface
    {
        return $this->client ??= new MockHttpClient();
    }
    protected function getLogger() : LoggerInterface
    {
        return $this->logger ??= $this->createMock(LoggerInterface::class);
    }
    protected function getDefaultLocale() : string
    {
        return $this->defaultLocale ??= 'en';
    }
    protected function getLoader() : LoaderInterface
    {
        return $this->loader ??= $this->createMock(LoaderInterface::class);
    }
    protected function getXliffFileDumper() : XliffFileDumper
    {
        return $this->xliffFileDumper ??= $this->createMock(XliffFileDumper::class);
    }
    protected function getTranslatorBag() : TranslatorBagInterface
    {
        return $this->translatorBag ??= $this->createMock(TranslatorBagInterface::class);
    }
}
