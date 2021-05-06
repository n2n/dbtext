import { Message } from 'src/app/util/i18n/message';
import { SiField } from '../../../si-field';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { Observable, BehaviorSubject } from 'rxjs';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { SiInputResetPoint } from '../../../si-input-reset-point';

export abstract class SiFieldAdapter implements SiField/*, MessageFieldModel*/ {
	protected messagesCollection = new BehaviorCollection<Message>([]);
	private disabledSubject = new BehaviorSubject<boolean>(false);

	abstract hasInput(): boolean;

	abstract readInput(): object;

	abstract createInputResetPoint(): Promise<SiInputResetPoint>;

	isDisplayable(): boolean {
		return true;
	}

	isDisabled(): boolean {
		return this.disabledSubject.getValue();
	}

	setDisabled(disabled: boolean): void {
		this.disabledSubject.next(disabled);
	}

	getDisabled$(): Observable<boolean> {
		return this.disabledSubject.asObservable();
	}

	abstract createUiStructureModel(compactMode: boolean): UiStructureModel;

	getMessages$(): Observable<Message[]> {
		return this.messagesCollection.get$();
	}

	getMessages(): Message[] {
		return this.messagesCollection.get();
	}

	handleError(messages: Message[]): void {
		this.messagesCollection.push(...messages);
	}

	resetError(): void {
		this.messagesCollection.clear();
	}
}
