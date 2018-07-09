<?php

declare(strict_types=1);

namespace PhpStormGen;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigTest extends TestCase
{
    public function testWillCreateConfigFromArray(): void
    {
        $config = new Config([]);

        $this->assertInstanceOf(Config::class, $config);
    }


    public function testStrictParametersCheck(): void
    {

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Unrecognized option "extraValue" under "root.parameters"');

        new Config(['parameter' => 1]);
    }

    public function testCanOverrideRootDefinition(): void
    {
        $configDefinition = new class extends ConfigDefinition implements ConfigurationInterface
        {
            protected function getRootDefinition(TreeBuilder $treeBuilder): ArrayNodeDefinition
            {
                $rootNode = parent::getRootDefinition($treeBuilder);
                $rootNode
                    ->children()
                    ->scalarNode('requiredRootNode')
                    ->isRequired()
                    ->cannotBeEmpty()
                ;
                return $rootNode;
            }
        };
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child node "requiredRootNode" at path "root" must be configured.');

        new Config([], $configDefinition);
    }

    public function testIsForwardCompatible(): void
    {
        $config = new Config(['yetNonexistentKey' => 'value']);
        $this->assertSame(['yetNonexistentKey' => 'value'], $config->getData());
    }

    public function testGettersWillNotFailIfKeyIsMissing(): void
    {
        $config = new Config([
            'lorem' => [
                'ipsum' => [
                    'dolores' => 'value',
                ],
            ],
        ]);
        $this->assertSame([], $config->getParameters());
        $this->assertSame('', $config->getAction());
        $this->assertSame([], $config->getAuthorization());
        $this->assertSame('', $config->getOAuthApiAppKey());
        $this->assertSame('', $config->getOAuthApiAppSecret());
        $this->assertSame('', $config->getOAuthApiData());
        $this->assertSame([], $config->getImageParameters());
        $this->assertSame([], $config->getStorage());
        $this->assertSame('', $config->getValue(['parameters', 'ipsum', 'dolor'], ''));
    }

    public function testGettersWillGetKeyIfPresent(): void
    {
        $configDefinition = new class extends ConfigDefinition implements ConfigurationInterface
        {
            protected function getParametersDefinition(): ArrayNodeDefinition
            {
                $nodeDefinition = parent::getParametersDefinition();
                // @formatter:off
                $nodeDefinition->isRequired();
                $nodeDefinition
                    ->children()
                    ->arrayNode('ipsum')
                        ->children()
                            ->scalarNode('dolor');
                // @formatter:on
                return $nodeDefinition;
            }
        };
        $config = new Config([
            'parameters' => [
                'ipsum' => [
                    'dolor' => 'value',
                ],
            ],
            'action' => 'run',
            'authorization' => [
                'oauth_api' => [
                    'credentials' => [
                        '#data' => 'value',
                        '#appSecret' => 'secret',
                        'appKey' => 'key',
                    ],
                ],
            ],
            'image_parameters' => ['param1' => 'value1'],
            'storage' => [
                'input' => [
                    'tables' => [],
                ],
                'output' => [
                    'files' => [],
                ],
            ],
        ], $configDefinition);
        $this->assertEquals(
            [
                'ipsum' => [
                    'dolor' => 'value',
                ],
            ],
            $config->getParameters()
        );
        $this->assertEquals(
            'run',
            $config->getAction()
        );
        $this->assertEquals(
            [
                'oauth_api' => [
                    'credentials' => [
                        '#data' => 'value',
                        '#appSecret' => 'secret',
                        'appKey' => 'key',
                    ],
                ],
            ],
            $config->getAuthorization()
        );
        $this->assertEquals(
            'value',
            $config->getOAuthApiData()
        );
        $this->assertEquals(
            'secret',
            $config->getOAuthApiAppSecret()
        );
        $this->assertEquals(
            'key',
            $config->getOAuthApiAppKey()
        );
        $this->assertEquals(
            ['param1' => 'value1'],
            $config->getImageParameters()
        );
        $this->assertEquals(
            [
                'input' => [
                    'tables' => [],
                ],
                'output' => [
                    'files' => [],
                ],
            ],
            $config->getStorage()
        );
        $this->assertEquals(
            'value',
            $config->getValue(['parameters', 'ipsum', 'dolor'])
        );
    }

    public function testWillGetRawDataWithoutDefaultValues(): void
    {
        $configDefinition = new class extends ConfigDefinition implements ConfigurationInterface
        {
            protected function getParametersDefinition(): ArrayNodeDefinition
            {
                $nodeDefinition = parent::getParametersDefinition();
                // @formatter:off
                $nodeDefinition->isRequired();
                $nodeDefinition
                    ->children()
                        ->scalarNode('requiredValue')
                            ->defaultValue('loremIpsum')
                            ->cannotBeEmpty();
                // @formatter:on
                return $nodeDefinition;
            }
        };

        $config = new Config(['parameters' => []], $configDefinition);
        $this->assertSame(['parameters' => ['requiredValue' => 'loremIpsum']], $config->getData());
        $this->assertSame(['parameters' => []], $config->getRawData());
    }
}
