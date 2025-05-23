#
# dot.profile
#
# part of pfSense (https://www.pfsense.org)
# Copyright (c) 2004-2013 BSD Perimeter
# Copyright (c) 2013-2016 Electric Sheep Fencing
# Copyright (c) 2014-2025 Rubicon Communications, LLC (Netgate)
# All rights reserved.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

# Detect interactive logins and display the shell
unset _interactive
if [ -n "${SSH_TTY}" ]; then
	_interactive=1
else
	case "${TERM}" in
	cons25|xterm|vt100|vt102|vt220)
		_interactive=1
		;;
	esac
fi

if [ -n "${_interactive}" ]; then
	/usr/bin/resizewin -z
	if [ -f ~/.profile.local ]; then
		. ~/.profile.local
	fi
	[ "$(id -u)" = "0" -a -f /etc/motd-passwd ] \
		&& cat /etc/motd-passwd
	/etc/rc.initial
	exit
fi
