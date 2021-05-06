import { NgModule } from '@angular/core';
import { RouterModule, Routes, UrlSegment, UrlMatchResult, UrlSegmentGroup, Route } from '@angular/router';
import { EiComponent } from 'src/app/op/comp/ei/ei.component';
import { FallbackComponent } from 'src/app/op/comp/common/fallback/fallback.component';
import { UsersComponent } from './user/comp/users/users.component';
import { UserComponent } from './user/comp/user/user.component';
import { ChPwComponent } from './user/comp/ch-pw/ch-pw.component';
import { ToolsComponent } from './tool/comp/tools/tools.component';
import { MailCenterComponent } from './tool/comp/mail-center/mail-center.component';

const routes: Routes = [
	{
		path: 'users', component: UsersComponent
	},
	{
		path: 'users/user/:userId', component: UserComponent
	},
	{
		path: 'users/chpw/:userId', component: ChPwComponent
	},
	{
		path: 'users/add', component: UserComponent
	},
	{
		path: 'tools', component: ToolsComponent
	},
	{
		path: 'tools/mail-center', component: MailCenterComponent
	},
	{
		/*path: 'manage', */component: EiComponent, matcher: matchesManageUrl
	},
	{
		path: '**', component: FallbackComponent
	}
];

@NgModule({
	imports: [ RouterModule.forRoot(routes /*, { enableTracing: true }*/, { relativeLinkResolution: 'legacy' })],
	exports: [ RouterModule ],
	providers: [	]
})
export class OpRoutingModule {}


export function matchesManageUrl(url: UrlSegment[], group: UrlSegmentGroup, route: Route): UrlMatchResult {
	if (url.length < 1 || url[0].path !== 'manage') {
		// alert('not found');
		return null as any;
	}

	return { consumed: url };
}

