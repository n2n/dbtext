<?php

namespace dbtext\storage;

class GroupDataRecord {
	function __construct(public readonly string $namespace, public readonly array $data) {
	}
}