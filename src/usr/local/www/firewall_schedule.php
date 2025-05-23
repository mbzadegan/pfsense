<?php
/*
 * firewall_schedule.php
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2004-2013 BSD Perimeter
 * Copyright (c) 2013-2016 Electric Sheep Fencing
 * Copyright (c) 2014-2025 Rubicon Communications, LLC (Netgate)
 * All rights reserved.
 *
 * originally based on m0n0wall (http://m0n0.ch/wall)
 * Copyright (c) 2003-2004 Manuel Kasper <mk@neon1.net>.
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

##|+PRIV
##|*IDENT=page-firewall-schedules
##|*NAME=Firewall: Schedules
##|*DESCR=Allow access to the 'Firewall: Schedules' page.
##|*MATCH=firewall_schedule.php*
##|-PRIV

define('CLOCK', '<i class="fa-regular fa-clock"></i>');

$dayArray = array (gettext('Mon'), gettext('Tues'), gettext('Wed'), gettext('Thur'), gettext('Fri'), gettext('Sat'), gettext('Sun'));
$monthArray = array (gettext('January'), gettext('February'), gettext('March'), gettext('April'), gettext('May'), gettext('June'), gettext('July'), gettext('August'), gettext('September'), gettext('October'), gettext('November'), gettext('December'));

require_once("guiconfig.inc");
require_once("filter.inc");
require_once("shaper.inc");
require_once("firewall_schedule.inc");

$pgtitle = array(gettext("Firewall"), gettext("Schedules"));

if ($_POST['act'] == "del") {
	$errmsg = deleteSchedule($_POST);
}

include("head.inc");

if ($errmsg) {
	print_info_box($errmsg, 'danger');
}
?>

<div class="panel panel-default">
	<div class="panel-heading"><h2 class="panel-title"><?=gettext('Schedules')?></h2></div>
	<div class="panel-body table-responsive">
		<table class="table table-striped table-hover table-condensed table-rowdblclickedit">
			<thead>
				<tr>
					<th><!--"Active" indicator--></th>
					<th><?=gettext("Name")?></th>
					<th><?=gettext("Range: Date / Times / Name")?></th>
					<th><?=gettext("Description")?></th>
					<th><?=gettext("Actions")?></th>
				</tr>
			</thead>
			<tbody>
<?php
$i = 0;
foreach (config_get_path('schedules/schedule', []) as $schedule):
	$schedstatus = filter_get_time_based_rule_status($schedule);
?>
				<tr>
					<td>
						<?=($schedstatus) ? '<a title="' . gettext("Schedule is currently active") . '">' . CLOCK . '</a>':''?>
					</td>
					<td>
						 <?=htmlspecialchars($schedule['name'])?>
					</td>
					<td>
<?php
	$first = true;
	foreach ($schedule['timerange'] as $timerange) {
		$tempFriendlyTime = "";
		$tempID = "";
		$firstprint = false;

		if ($timerange) {
			$dayFriendly = "";
			$tempFriendlyTime = "";

			//get hours
			$temptimerange = $timerange['hour'];
			$temptimeseparator = strrpos($temptimerange, "-");

			$starttime = substr ($temptimerange, 0, $temptimeseparator);
			$stoptime = substr ($temptimerange, $temptimeseparator+1);

			if ($timerange['month']) {
				$tempmontharray = explode(",", $timerange['month']);
				$tempdayarray = explode(",", $timerange['day']);
				$arraycounter = 0;
				$firstDayFound = false;
				$firstPrint = false;
				foreach ($tempmontharray as $monthtmp) {
					$month = (int)$tempmontharray[$arraycounter];
					$day = (int)$tempdayarray[$arraycounter];

					if (!$firstDayFound) {
						$firstDay = $day;
						$firstmonth = $month;
						$firstDayFound = true;
					}

					$currentDay = $day;
					$nextDay = $tempdayarray[$arraycounter+1];
					$currentDay++;

					if (($currentDay != $nextDay) || ($tempmontharray[$arraycounter] != $tempmontharray[$arraycounter+1])) {
						if ($firstPrint) {
							$dayFriendly .= "<br />";
						}

						$currentDay--;

						if ($currentDay != $firstDay) {
							$dayFriendly .= $monthArray[$firstmonth-1] . " " . $firstDay . " - " . $currentDay ;
						} else {
							$dayFriendly .=	 $monthArray[$month-1] . " " . $day;
						}

						$firstDayFound = false;
						$firstPrint = true;
					}
					$arraycounter++;
				}
			} else {
				$tempdayFriendly = $timerange['position'];
				$firstDayFound = false;
				$tempFriendlyDayArray = explode(",", $tempdayFriendly);
				$currentDay = "";
				$firstDay = "";
				$nextDay = "";
				$counter = 0;

				foreach ($tempFriendlyDayArray as $day) {
					if ($day != "") {
						if (!$firstDayFound) {
							$firstDay = $tempFriendlyDayArray[$counter];
							$firstDayFound = true;
						}

						$currentDay =$tempFriendlyDayArray[$counter];
						//get next day
						$nextDay = $tempFriendlyDayArray[$counter+1];
						$currentDay++;

						if ($currentDay != $nextDay) {
							if ($firstprint) {
								$dayFriendly .= "<br />";
							}

							$currentDay--;

							if ($currentDay != $firstDay) {
								$dayFriendly .= $dayArray[$firstDay-1] . " - " . $dayArray[$currentDay-1];
							} else {
								$dayFriendly .= $dayArray[$firstDay-1];
							}

							$firstDayFound = false;
							$firstprint = true;
						}
						$counter++;
					}
				}
			}

			$timeFriendly = $starttime . "-" . $stoptime;
			$description = htmlspecialchars($timerange['rangedescr']);

			print(($first ? '':'<br />') . $dayFriendly . ' / ' . $timeFriendly . ' / ' . $description);
		}
	$first = false;
	}
?>
					</td>

					<td>
						<?=htmlspecialchars($schedule['descr'])?>&nbsp;
					</td>

					<td>
						<a class="fa-solid fa-pencil" title="<?=gettext("Edit schedule"); ?>" href="firewall_schedule_edit.php?id=<?=$i?>"></a>
						<a class="fa-solid fa-trash-can" title="<?=gettext("Delete schedule")?>" href="firewall_schedule.php?act=del&amp;id=<?=$i?>" usepost></a>

					</td>
				</tr>
<?php
	$i++;
endforeach;
?>
			</tbody>
		</table>
	</div>
</div>

<?=($i > 0) ? CLOCK . gettext(' Indicates that the schedule is currently active.'):''?>

<nav class="action-buttons">
	<a href="firewall_schedule_edit.php" class="btn btn-sm btn-success">
		<i class="fa-solid fa-plus icon-embed-btn"></i>
		<?=gettext("Add")?>
	</a>
</nav>

<div class="infoblock">
	<?php print_info_box(gettext('Schedules act as placeholders for time ranges to be used in firewall rules.'), 'info', false); ?>
</div>

<?php

include("foot.inc");
