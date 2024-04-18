<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassMethodAssignManipulator
{
    /**
     * @var array<string, string[]>
     */
    private $alreadyAddedClassMethodNames = [];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function addParameterAndAssignToMethod(ClassMethod $classMethod, string $name, ?Type $type, Assign $assign) : void
    {
        if ($this->hasMethodParameter($classMethod, $name)) {
            return;
        }
        $classMethod->params[] = $this->nodeFactory->createParamFromNameAndType($name, $type);
        $classMethod->stmts[] = new Expression($assign);
        $classMethodHash = \spl_object_hash($classMethod);
        $this->alreadyAddedClassMethodNames[$classMethodHash][] = $name;
    }
    private function hasMethodParameter(ClassMethod $classMethod, string $name) : bool
    {
        foreach ($classMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param->var, $name)) {
                return \true;
            }
        }
        $classMethodHash = \spl_object_hash($classMethod);
        if (!isset($this->alreadyAddedClassMethodNames[$classMethodHash])) {
            return \false;
        }
        return \in_array($name, $this->alreadyAddedClassMethodNames[$classMethodHash], \true);
    }
}