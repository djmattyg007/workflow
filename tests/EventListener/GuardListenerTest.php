<?php

/*
 * This file was forked from the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Matthew Gamble <git@matthewgamble.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MattyG\StateMachine\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use MattyG\StateMachine\Event\GuardEvent;
use MattyG\StateMachine\EventListener\ExpressionLanguage;
use MattyG\StateMachine\EventListener\GuardExpression;
use MattyG\StateMachine\EventListener\GuardListener;
use MattyG\StateMachine\Tests\Subject;
use MattyG\StateMachine\Transition;
use MattyG\StateMachine\StateMachineInterface;

class GuardListenerTest extends TestCase
{
    private $authenticationChecker;
    private $validator;
    private $listener;
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = [
            'test_is_granted' => 'is_granted("something")',
            'test_is_valid' => 'is_valid(subject)',
            'test_expression' => [
                new GuardExpression(new Transition('name', 'from', 'to'), '!is_valid(subject)'),
                new GuardExpression(new Transition('name', 'from', 'to'), 'is_valid(subject)'),
            ],
        ];
        $expressionLanguage = new ExpressionLanguage();
        $token = new UsernamePasswordToken('username', 'credentials', 'provider', ['ROLE_USER']);
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->any())->method('getToken')->willReturn($token);
        $this->authenticationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();
        $this->validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $roleHierarchy = new RoleHierarchy([]);
        $this->listener = new GuardListener($this->configuration, $expressionLanguage, $tokenStorage, $this->authenticationChecker, $trustResolver, $roleHierarchy, $this->validator);
    }

    protected function tearDown(): void
    {
        $this->authenticationChecker = null;
        $this->validator = null;
        $this->listener = null;
    }

    public function testWithNotSupportedEvent()
    {
        $event = $this->createEvent();
        $this->configureAuthenticationChecker(false);
        $this->configureValidator(false);

        $this->listener->onTransition($event, 'not supported');

        $this->assertFalse($event->isBlocked());
    }

    public function testWithSecuritySupportedEventAndReject()
    {
        $event = $this->createEvent();
        $this->configureAuthenticationChecker(true, false);

        $this->listener->onTransition($event, 'test_is_granted');

        $this->assertTrue($event->isBlocked());
    }

    public function testWithSecuritySupportedEventAndAccept()
    {
        $event = $this->createEvent();
        $this->configureAuthenticationChecker(true, true);

        $this->listener->onTransition($event, 'test_is_granted');

        $this->assertFalse($event->isBlocked());
    }

    public function testWithValidatorSupportedEventAndReject()
    {
        $event = $this->createEvent();
        $this->configureValidator(true, false);

        $this->listener->onTransition($event, 'test_is_valid');

        $this->assertTrue($event->isBlocked());
    }

    public function testWithValidatorSupportedEventAndAccept()
    {
        $event = $this->createEvent();
        $this->configureValidator(true, true);

        $this->listener->onTransition($event, 'test_is_valid');

        $this->assertFalse($event->isBlocked());
    }

    public function testWithGuardExpressionWithNotSupportedTransition()
    {
        $event = $this->createEvent();
        $this->configureValidator(false);
        $this->listener->onTransition($event, 'test_expression');

        $this->assertFalse($event->isBlocked());
    }

    public function testWithGuardExpressionWithSupportedTransition()
    {
        $event = $this->createEvent($this->configuration['test_expression'][1]->getTransition());
        $this->configureValidator(true, true);
        $this->listener->onTransition($event, 'test_expression');

        $this->assertFalse($event->isBlocked());
    }

    public function testGuardExpressionBlocks()
    {
        $event = $this->createEvent($this->configuration['test_expression'][1]->getTransition());
        $this->configureValidator(true, false);
        $this->listener->onTransition($event, 'test_expression');

        $this->assertTrue($event->isBlocked());
    }

    private function createEvent(Transition $transition = null)
    {
        $subject = new Subject('from');
        $transition = $transition ?: new Transition('name', 'from', 'to');

        $stateMachine = $this->getMockBuilder(StateMachineInterface::class)->getMock();

        return new GuardEvent($subject, $transition, $stateMachine);
    }

    private function configureAuthenticationChecker($isUsed, $granted = true)
    {
        if (!$isUsed) {
            $this->authenticationChecker
                ->expects($this->never())
                ->method('isGranted')
            ;

            return;
        }

        $this->authenticationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn($granted)
        ;
    }

    private function configureValidator($isUsed, $valid = true)
    {
        if (!$isUsed) {
            $this->validator
                ->expects($this->never())
                ->method('validate')
            ;

            return;
        }

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList($valid ? [] : [new ConstraintViolation('a violation', null, [], '', null, '')]))
        ;
    }
}
