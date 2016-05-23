<?php
module_load_include('php', 'vals_soc', 'includes/classes/StatelessTimeline');
class Timeline {

	public static function getInstance($timelineTestDate=NULL){
		self::setupSession($timelineTestDate);
		return StatelessTimeline::getInstance();
	}
	
	private static function setupSession($timelineTestDate=NULL){
		if (!isset($_SESSION)){
			session_id('TimelineMultipageSession');
			drupal_session_start();
		}
		if (isset($timelineTestDate)){
			$_SESSION['timelineDate'] = $timelineTestDate;
			StatelessTimeline::getInstance()->setDummyTestDate($_SESSION['timelineDate']);
			watchdog('setting NOW from new value', $timelineTestDate);
			return;
		}
		if (isset($_SESSION['timelineDate'])){
			watchdog('setting NOW from value found in session',$_SESSION['timelineDate']);
			StatelessTimeline::getInstance()->setDummyTestDate($_SESSION['timelineDate']);
		}
		
	}
}