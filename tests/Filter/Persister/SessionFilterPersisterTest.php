<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Filter\Persister;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Filter\Persister\SessionFilterPersister;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionFilterPersisterTest extends TestCase
{
    /**
     * @var SessionInterface&MockObject
     */
    private $session;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->session);
    }

    public function testGetDefaultValueFromSessionIfNotDefined(): void
    {
        $this->session->expects(static::once())->method('get')
            ->with('admin.customer.filter.parameters', [])
            ->willReturn([]);

        static::assertSame([], $this->createPersister()->get('admin.customer'));
    }

    public function testGetValueFromSessionIfDefined(): void
    {
        $filters = [
            DatagridInterface::PAGE => 1,
            DatagridInterface::SORT_BY => 'firstName',
            DatagridInterface::SORT_ORDER => 'ASC',
            DatagridInterface::PER_PAGE => 25,
        ];
        $this->session->expects(static::once())->method('get')
            ->with('admin.customer.filter.parameters', [])
            ->willReturn($filters);

        static::assertSame($filters, $this->createPersister()->get('admin.customer'));
    }

    public function testSetValueToSession(): void
    {
        $filters = [
            DatagridInterface::PAGE => 1,
            DatagridInterface::SORT_BY => 'firstName',
            DatagridInterface::SORT_ORDER => 'ASC',
            DatagridInterface::PER_PAGE => 25,
        ];
        $this->session->expects(static::once())->method('set')
            ->with('admin.customer.filter.parameters', $filters)
            ->willReturn(null);

        $this->createPersister()->set('admin.customer', $filters);
    }

    public function testResetValueToSession(): void
    {
        $this->session->expects(static::once())->method('remove')
            ->with('admin.customer.filter.parameters')
            ->willReturn(null);

        $this->createPersister()->reset('admin.customer');
    }

    private function createPersister(): SessionFilterPersister
    {
        $request = new Request();
        $request->setSession($this->session);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new SessionFilterPersister($requestStack);
    }
}
