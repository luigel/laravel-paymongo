<?php

use Luigel\Paymongo\Facades\Paymongo;

$schedule = Paymongo::payouts()->schedule('org_9NxTZ8ZDVQpZC3bDMSKtwEXA'); // your organization id

$schedule->scheduleType;      // your current schedule, e.g. "weekly"
$schedule->options;           // list<string>: the schedules you can switch to
$schedule->attribute('days'); // the weekday(s) or date of month it pays out on

// The next payout: its amount in centavos, receive_at, and a breakdown
// of the payments, refunds, disputes, and adjustments in it.
$upcoming = $schedule->lineup[0] ?? null;
