<?php

namespace Cruxinator\ResponseModel;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Cruxinator\Package\Strings\MyStr;
use Cruxinator\Package\Strings\Stringable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;

abstract class ResponseModel implements Arrayable, Responsable, ArrayAccess
{
    protected $ignore = [];

    protected $methodMap = [];

    protected $view = '';

    protected $prettyPrintJson = false;

    public function toArray(): array
    {
        return $this
            ->items()
            ->map(function ($item) {
                return $item instanceof Arrayable ? $item->toArray() : $item;
            })
            ->all();
    }

    /** TODO: Items are converted to an array, regardless of target being view or json - should add check for that */
    public function toResponse($request): Response
    {
        assert($this->isOK(), 'ResponseModel is not ok');
        if (
            $request->wantsJson() ||
            null === $this->view ||
            empty($this->view)
        ) {
            return new JsonResponse(
                $this->items(),
                $this->getResponseCode(),
                $this->getHeaders(),
                $this->getJsonOptions());
        }

        return response()->view($this->view, $this);
    }

    protected function getResponseCode(): int
    {
        return 200;
    }

    protected function getHeaders():array
    {
        return [];
    }

    protected function getJsonOptions():int
    {
        return $this->prettyPrintJson ? JSON_PRETTY_PRINT : 0;
    }

    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    protected function items(): Collection
    {
        $class = new ReflectionClass($this);

        $publicProperties = collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(function (ReflectionProperty $property) {
                return $this->shouldIgnore($property->getName());
            })
            ->mapWithKeys(function (ReflectionProperty $property) {
                return [$property->getName() => $this->{$property->getName()}];
            });

        $publicMethods = collect($class->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(function (ReflectionMethod $method) {
                return $this->shouldIgnore($method->getName());
            })
            ->mapWithKeys(function (ReflectionMethod $method) {
                return [$this->methodMap[$method->getName()] ?? $method->getName() => $this->createVariableFromMethod($method)];
            });

        return $publicProperties->merge($publicMethods);
    }

    protected function shouldIgnore(string $methodName): bool
    {
        if (MyStr::startsWith($methodName, '__')) {
            return true;
        }

        return in_array($methodName, $this->ignoredMethods());
    }

    protected function ignoredMethods(): array
    {
        // condensed version of
        //$publicMethodNames = [];
        //foreach((new ReflectionClass(ResponseModel::class))->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod){
        //$publicMethodNames[] = $reflectionMethod->getName();
        //}
        //return array_merge($publicMethodNames,$this->ignore);

        return array_merge(array_map(function (ReflectionMethod $method) {
            return $method->getName();
        },
        (new ReflectionClass(self::class))->getMethods(ReflectionMethod::IS_PUBLIC)), $this->ignore);
    }

    protected function createVariableFromMethod(ReflectionMethod $method)//: Closure
    {
        if ($method->getNumberOfParameters() === 0) {
            return $this->{$method->getName()}();
        }

        return Closure::fromCallable([$this, $method->getName()]);
    }

    protected function addIgnore(array $items):self
    {
        $this->ignore = array_merge($items, $this->ignore);
        return $this;
    }

    protected function addMethodMap(string $methodName, string $mapsToProperty): self
    {
        $this->methodMap[$methodName] = $mapsToProperty;

        return $this;
    }

    public function isOK(): bool
    {
        return true;
    }

    public function __construct()
    {
    }

    public function enableGetterToSnakeMethodMap(): void
    {
        collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(function (ReflectionMethod $method) {
                return MyStr::startsWith($method->getName(), 'get') &&
                    $method->getNumberOfParameters() === 0;
            })
            ->map(function (ReflectionMethod $method) {
                return MyStr::asStringable($method->getName());
            })
            ->each(function (Stringable $methodName) {
                $this->addMethodMap(
                    strval($methodName),
                    $methodName->after('get')->snake()
                );
            });
    }

    public function offsetExists($offset): bool
    {
        return $this->items()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->items()->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $reverseMethodMap = array_keys($this->methodMap);
        if (isset($reverseMethodMap[$offset]) &&
            method_exists($this, $method = 's'.MyStr::substr($reverseMethodMap[$offset], 1))) {
            $this->{$method}($value);
        } elseif ($this->offsetExists($offset)) {
            $this->{$offset} = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            unset($this->{$offset});
        }
        $reverseMethodMap = array_keys($this->methodMap);
        if (isset($reverseMethodMap[$offset])) {
            $this->addIgnore($reverseMethodMap[$offset]);
        }
    }

    protected function ignoreSetters(): void
    {
        $this->addIgnore(
            collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
                ->filter(function (ReflectionMethod $method) {
                    $name = $method->getName();

                    return 3 < strlen($name) && MyStr::startsWith($name, 'set') &&
                        $name[3] === strtoupper($name[3]);
                })
                ->map(function (ReflectionMethod $method) {
                    return $method->getName();
                })
                ->toArray()
        );
    }
}
