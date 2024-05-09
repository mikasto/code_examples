<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder\Replacer\SpecifiersConfig;

use FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier\SpecifierArray;
use FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier\SpecifierFloat;
use FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier\SpecifierIdentity;
use FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier\SpecifierInteger;
use FpDbTest\QueryBuilder\Replacer\SpecifiersConfig\Specifier\SpecifierMixed;

class SpecifiersConfigFull extends SpecifiersConfigAbstract
{
	public function getList(): array
	{
		return [
			new SpecifierInteger($this->mysqli),
			new SpecifierFloat($this->mysqli),
			new SpecifierArray($this->mysqli),
			new SpecifierIdentity($this->mysqli),
			new SpecifierMixed($this->mysqli),
		];
	}
}
