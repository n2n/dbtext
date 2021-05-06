export class DateUtils {
	static sqlToDate(sqlDateString: string|null): Date|null {
		if (!sqlDateString) {
			return null;
		}

		const date = new Date(sqlDateString);
		if (DateUtils.dateToSql(date) !== sqlDateString) {
			throw new Error('invalid sql date format. The format must be like ' + DateUtils.dateToSql(new Date())
					+ '.');
		}

		return date;
	}

	static dateToSql(date: Date|null): string|null {
		if (!date) {
			return null;
		}

		return date.getFullYear() + '-'
				+ DateUtils.fillWithLeadingZeros(date.getMonth() + 1, 2) + '-'
				+ DateUtils.fillWithLeadingZeros(date.getDate(), 2) + ' '
				+ DateUtils.fillWithLeadingZeros(date.getHours(), 2) + ':'
				+ DateUtils.fillWithLeadingZeros(date.getMinutes(), 2) + ':'
				+ DateUtils.fillWithLeadingZeros(date.getSeconds(), 2);
	}

	private static fillWithLeadingZeros(numberToFormat: number, numDecimals: number): string {
		return (new Array(numDecimals).join('0') + numberToFormat).slice(-numDecimals);
	}
}
