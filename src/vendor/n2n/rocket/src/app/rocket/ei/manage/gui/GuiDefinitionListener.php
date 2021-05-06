<?php
namespace rocket\ei\manage\gui;

interface GuiDefinitionListener {
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 */
	public function onNewEiGuiFrame(EiGuiFrame $eiGuiFrame);
	
	
// 	/**
// 	 * @param EiEntryGui $eiEntryGui
// 	 */
// 	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);
	
// 	/**
// 	 * @param HtmlView $view
// 	 */
// 	public function onNewView(HtmlView $view);
}