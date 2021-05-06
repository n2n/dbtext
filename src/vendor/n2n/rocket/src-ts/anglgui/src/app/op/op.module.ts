import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EiComponent } from './comp/ei/ei.component';
import { OpRoutingModule } from 'src/app/op/op-routing.module';
import { UiModule } from 'src/app/ui/ui.module';
import { FallbackComponent } from 'src/app/op/comp/common/fallback/fallback.component';
import { HttpClientModule } from '@angular/common/http';
import { SiModule } from '../si/si.module';
import { UsersComponent } from './user/comp/users/users.component';
import { UtilModule } from '../util/util.module';
import { UserComponent } from './user/comp/user/user.component';
import { FormsModule } from '@angular/forms';
import { ChPwComponent } from './user/comp/ch-pw/ch-pw.component';
import { ToolsComponent } from './tool/comp/tools/tools.component';
import { MailCenterComponent } from './tool/comp/mail-center/mail-center.component';

@NgModule({
	declarations: [EiComponent, FallbackComponent, UsersComponent, UserComponent, ChPwComponent, ToolsComponent, MailCenterComponent ],
	imports: [
		CommonModule,
		OpRoutingModule,
		UiModule,
		HttpClientModule,
		SiModule,
		UtilModule,
		FormsModule
	]
})
export class OpModule { }
