<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\DeadCode\PhpDoc\Guard\StandaloneTypeRemovalGuard;
use Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer;
use Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
final class DeadReturnTagValueNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer
     */
    private $genericTypeNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer
     */
    private $mixedArrayTypeNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\Guard\StandaloneTypeRemovalGuard
     */
    private $standaloneTypeRemovalGuard;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(TypeComparator $typeComparator, BetterNodeFinder $betterNodeFinder, GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer, MixedArrayTypeNodeAnalyzer $mixedArrayTypeNodeAnalyzer, StandaloneTypeRemovalGuard $standaloneTypeRemovalGuard, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->typeComparator = $typeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->genericTypeNodeAnalyzer = $genericTypeNodeAnalyzer;
        $this->mixedArrayTypeNodeAnalyzer = $mixedArrayTypeNodeAnalyzer;
        $this->standaloneTypeRemovalGuard = $standaloneTypeRemovalGuard;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function isDead(ReturnTagValueNode $returnTagValueNode, ClassMethod $classMethod) : bool
    {
        $returnType = $classMethod->getReturnType();
        if ($returnType === null) {
            return \false;
        }
        $classLike = $this->betterNodeFinder->findParentType($classMethod, ClassLike::class);
        if ($classLike instanceof Trait_ && $returnTagValueNode->type instanceof ThisTypeNode) {
            return \false;
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($returnType, $returnTagValueNode->type, $classMethod)) {
            return \false;
        }
        if ($this->phpDocTypeChanger->isAllowed($returnTagValueNode->type)) {
            return \false;
        }
        if (!$returnTagValueNode->type instanceof BracketsAwareUnionTypeNode) {
            return $this->isIdentiferRemovalAllowed($returnTagValueNode, $returnType);
        }
        if ($this->genericTypeNodeAnalyzer->hasGenericType($returnTagValueNode->type)) {
            return \false;
        }
        if ($this->mixedArrayTypeNodeAnalyzer->hasMixedArrayType($returnTagValueNode->type)) {
            return \false;
        }
        if ($this->hasTruePseudoType($returnTagValueNode->type)) {
            return \false;
        }
        return $returnTagValueNode->description === '';
    }
    private function isIdentiferRemovalAllowed(ReturnTagValueNode $returnTagValueNode, Node $node) : bool
    {
        if ($returnTagValueNode->description === '') {
            return $this->standaloneTypeRemovalGuard->isLegal($returnTagValueNode->type, $node);
        }
        return \false;
    }
    private function hasTruePseudoType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        $unionTypes = $bracketsAwareUnionTypeNode->types;
        foreach ($unionTypes as $unionType) {
            if (!$unionType instanceof IdentifierTypeNode) {
                continue;
            }
            $name = \strtolower((string) $unionType);
            if ($name === 'true') {
                return \true;
            }
        }
        return \false;
    }
}
