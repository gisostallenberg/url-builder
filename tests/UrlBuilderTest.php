<?php

namespace GisoStallenberg\UrlBuilder\Tests;

use GisoStallenberg\UrlBuilder\UrlBuilder;
use PHPUnit\Framework\TestCase;

final class UrlBuilderTest extends TestCase
{
    private const FULL = 'https://user:pass@example.com:8080/path/to?foo=bar&baz=qux#frag';

    public function testParsesAllComponents(): void
    {
        $b = new UrlBuilder(self::FULL);

        $this->assertSame('https', $b->getScheme());
        $this->assertSame('example.com', $b->getHost());
        $this->assertSame('8080', $b->getPort());
        $this->assertSame('user', $b->getUser());
        $this->assertSame('pass', $b->getPass());
        $this->assertSame('/path/to', $b->getPath());
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $b->getQuery());
        $this->assertSame('frag', $b->getFragment());
    }

    public function testRoundTripsFullUrl(): void
    {
        $this->assertSame(self::FULL, (string) new UrlBuilder(self::FULL));
        $this->assertSame(self::FULL, (new UrlBuilder(self::FULL))->getUrl());
    }

    public function testCreateFromUrl(): void
    {
        $url = 'https://example.com/path?foo=bar';
        $this->assertSame($url, UrlBuilder::createFromUrl($url)->getUrl());
    }

    public function testBuildsFromScratchWithSetters(): void
    {
        $url = (new UrlBuilder())
            ->setScheme('https')
            ->setHost('example.com')
            ->setPath('/a/b')
            ->getUrl();

        $this->assertSame('https://example.com/a/b', $url);
    }

    public function testEmptyBuilderIsEmptyString(): void
    {
        $this->assertSame('', (string) new UrlBuilder());
    }

    public function testSetQueryFromStringParsesToArray(): void
    {
        $b = (new UrlBuilder())->setQuery('a=1&b=2');
        $this->assertSame(['a' => '1', 'b' => '2'], $b->getQuery());
    }

    public function testSetQueryFromArrayRendersInUrl(): void
    {
        $url = (new UrlBuilder())->setScheme('https')->setHost('h')->setQuery(['a' => '1', 'b' => '2'])->getUrl();
        $this->assertSame('https://h?a=1&b=2', $url);
    }

    public function testArrayQueryRoundTrips(): void
    {
        $b = new UrlBuilder('https://h/?a=1&b[]=2&b[]=3');
        $this->assertSame(['a' => '1', 'b' => ['2', '3']], $b->getQuery());

        $again = new UrlBuilder($b->getUrl());
        $this->assertSame(['a' => '1', 'b' => ['2', '3']], $again->getQuery());
    }

    public function testAppendToQueryAddsScalarKey(): void
    {
        $b = (new UrlBuilder())->appendToQuery('a', '1');
        $this->assertSame(['a' => '1'], $b->getQuery());
    }

    public function testAppendToQueryPushesOntoExistingArray(): void
    {
        $b = (new UrlBuilder())->setQuery(['a' => ['1']])->appendToQuery('a', '2');
        $this->assertSame(['a' => ['1', '2']], $b->getQuery());
    }

    public function testUnsetInQueryRemovesKey(): void
    {
        $b = (new UrlBuilder())->setQuery(['a' => '1', 'b' => '2'])->unsetInQuery('a');
        $this->assertSame(['b' => '2'], $b->getQuery());
    }

    public function testUnsetInQueryWithValueOnlyRemovesOnMatch(): void
    {
        $b = (new UrlBuilder())->setQuery(['a' => '1']);

        $b->unsetInQuery('a', 'nope');
        $this->assertSame(['a' => '1'], $b->getQuery());

        $b->unsetInQuery('a', '1');
        $this->assertSame([], $b->getQuery());
    }

    public function testCreateFromComponents(): void
    {
        $b = UrlBuilder::createFromComponents([
            'scheme' => 'https',
            'host' => 'example.com',
            'path' => '/x',
            'query' => 'a=1',
        ]);

        $this->assertSame('https://example.com/x?a=1', $b->getUrl());
    }

    public function testGetComponentsOmitsEmpties(): void
    {
        $this->assertSame(
            [
                'scheme' => 'https',
                'host' => 'example.com',
                'port' => '8080',
                'user' => 'user',
                'pass' => 'pass',
                'path' => '/path/to',
                'query' => ['foo' => 'bar', 'baz' => 'qux'],
                'fragment' => 'frag',
            ],
            (new UrlBuilder(self::FULL))->getComponents(),
        );
    }

    public function testCreateFromGlobals(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/path?x=1';
        unset($_SERVER['HTTPS']);
        $this->assertSame('http://example.com/path?x=1', UrlBuilder::createFromGlobals()->getUrl());

        $_SERVER['HTTPS'] = 'on';
        $this->assertSame('https://example.com/path?x=1', UrlBuilder::createFromGlobals()->getUrl());
    }
}
