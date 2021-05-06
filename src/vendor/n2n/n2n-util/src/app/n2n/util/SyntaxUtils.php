<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\util;

use n2n\util\formatter\SqlFormatter;

class SyntaxUtils {
	
	/**
	 * @param string $queryString
	 * @return string
	 */
	public static function formatSql(string $queryString): string {
		return SqlFormatter::formatSql($queryString);
	}
}


/*
SELECT COALESCE(SUM(ti.price), 0) total
FROM transaction_item ti 
JOIN appointment a ON ti.appointment_id = a.id 
LEFT JOIN stylist ON a.stylist_id = stylist.id 
RIGHT JOIN transaction_item_type tit ON ti.transaction_item_type_id = tit.id 
CROSS JOIN (
	SELECT appointment_id, SUM(amount) amount 
	FROM payment ::
	GROUP BY appointment_id
) p ON p.appointment_id = a.id 
WHERE a.start_time >= ? AND a.start_time <= ? AND tit.code = ' SELECT ')
*/
