// block_cmanager is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// block_cmanager is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// COURSE REQUEST MANAGER BLOCK FOR MOODLE
// by Kyle Goslin & Daniel McSweeney
// Copyright 2013-2018 - Institute of Technology Blanchardstown.


------------------------------------------------
Course Request Manager for Moodle

by Kyle Goslin & Daniel McSweeney, Modified by Jeya Prakash K.

------------------------------------------------



  CRM Current Version 5.1
  For Moodle: 3.5
 

------------------------------------------------


Description:

This block is allows administrators to streamline the course creation
process at the start of the semester by allowing educators to make
requests for courses to be created.

Once requested, the Moodle administrator is responsible for accepting or 
denying requests for courses. If the administrator is not happy, a comment 
feature allows a conversation to take place between them and the educator about
the pending request.

During the process of creating a course request, additional information 
about the course can be collected from the educator such as course title, 
course codes and semester information. This block also offers additional 
customizable fields to allow the administrator to collect additional course 
information.


== Installation ==

To install this block, simply drop the entire cmanager folder into your
moodle/blocks folder. Go to Site Administration >> Notifications to install the plugin.

Once this has been done, navigate back to your site frontpage and add an instance
of the block. 
NOTE: You can add the block elsewhere but we recommend the site frontpage

Once you have done this, a short script will run setting up cmanager's environment
variables. This script will notify you when it is finished.

Then make sure you enter the config settings for the block (visible from the block) and 
configure the request block for new users.

You will also ned to set permissions for accessing the block and making requests etc.
See the section on permissions for more information on this.

For more assistance please check out the plugin page on moodle.org



==Permissions==


1. Create a new site role called ???Course Requestor??? e.g. Site Administration >> Users >> Permissions >> Define Roles >> Add new role
2. Base the role on Authenticated User
3. Allow the role to be assigned at System level 
4. Grant the following permissions for Block:	
	Add Record (block/cmanager:addrecord)
	View Record (block/cmanager:viewrecord)
5. Assign some users to this new role.

Thats it. You should be good to go.

You can also remove or add permissions to the manager and course creator roles using define permissions.
