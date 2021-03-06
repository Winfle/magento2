<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\Module\Dir\File
 */
namespace Magento\Framework\Module\Test\Unit\Dir;

use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_protFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileIteratorFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryReadFactoryMock;

    protected function setUp()
    {
        $this->_protFactoryMock = $this->getMock(
            \Magento\Framework\App\Config\BaseFactory::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->_dirsMock = $this->getMock(\Magento\Framework\Module\Dir::class, [], [], '', false, false);
        $this->_baseConfigMock = $this->getMock(
            \Magento\Framework\App\Config\Base::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->_moduleListMock = $this->getMock(\Magento\Framework\Module\ModuleListInterface::class);
        $this->directoryReadFactoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadFactory::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->_fileIteratorFactory = $this->getMock(
            \Magento\Framework\Config\FileIteratorFactory::class,
            [],
            [],
            '',
            false,
            false
        );

        $this->_model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            $this->_fileIteratorFactory,
            $this->directoryReadFactoryMock
        );
    }

    public function testGetModuleDirWhenCustomDirIsNotSet()
    {
        $this->_dirsMock->expects(
            $this->any()
        )->method(
            'getDir'
        )->with(
            'Test_Module',
            'etc'
        )->will(
            $this->returnValue('app/code/Test/Module/etc')
        );
        $this->assertEquals(
            'app/code/Test/Module/etc',
            $this->_model->getModuleDir(Dir::MODULE_ETC_DIR, 'Test_Module')
        );
    }

    public function testGetModuleDirWhenCustomDirIsSet()
    {
        $moduleDir = 'app/code/Test/Module/etc/custom';
        $this->_dirsMock->expects($this->never())->method('getDir');
        $this->_model->setModuleDir('Test_Module', 'etc', $moduleDir);
        $this->assertEquals($moduleDir, $this->_model->getModuleDir(Dir::MODULE_ETC_DIR, 'Test_Module'));
    }

    public function testGetConfigurationFiles()
    {
        $configPath = 'app/code/Test/Module/etc/config.xml';
        $modulesDirectoryMock = $this->getMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->will($this->returnValue(true));
        $this->directoryReadFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($modulesDirectoryMock));

        $this->_moduleListMock->expects($this->once())->method('getNames')->will($this->returnValue(['Test_Module']));
        $model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            new FileIteratorFactory(
                new \Magento\Framework\Filesystem\File\ReadFactory(new \Magento\Framework\Filesystem\DriverPool())
            ),
            $this->directoryReadFactoryMock
        );
        $model->setModuleDir('Test_Module', 'etc', 'app/code/Test/Module/etc');

        $this->assertEquals($configPath, $model->getConfigurationFiles('config.xml')->key());
    }

    public function testGetComposerJsonFiles()
    {
        $configPath = 'app/code/Test/Module/composer.json';
        $modulesDirectoryMock = $this->getMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->will($this->returnValue(true));
        $this->directoryReadFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($modulesDirectoryMock));

        $this->_moduleListMock->expects($this->once())->method('getNames')->will($this->returnValue(['Test_Module']));
        $model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            new FileIteratorFactory(
                new \Magento\Framework\Filesystem\File\ReadFactory(new \Magento\Framework\Filesystem\DriverPool())
            ),
            $this->directoryReadFactoryMock
        );
        $model->setModuleDir('Test_Module', '', 'app/code/Test/Module');

        $this->assertEquals($configPath, $model->getComposerJsonFiles()->key());
    }
}
