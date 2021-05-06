import { SplitContextInSiField } from './split-context-in-si-field';


describe('SplitContextInSiField', () => {
	let splitContextInSiField: SplitContextInSiField;

	beforeEach(() => {
		splitContextInSiField = new SplitContextInSiField();
	});

	it('should create', () => {
		expect(!!splitContextInSiField).toBeTrue();
	});
});
