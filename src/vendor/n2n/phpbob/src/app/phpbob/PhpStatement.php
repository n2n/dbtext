<?php
namespace phpbob;

interface PhpStatement {
	public function __toString();
	public function getLines(): array;
	public function getCode(): string;
	public function getPrependingCommentLines(): array;
}