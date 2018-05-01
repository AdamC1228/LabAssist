# Programmers Manual
==================

This manual details things that programmers who will be working on the system
should know that are not made clear elsewhere.

## Overview
===========
This project is split into three main sections. The database, the web
application, and a pair of background daemons. Each one has comments attached to
it in the ways that make sense for the language they are written in, so that
this manual serves to deal with the varying bits that can not be adequately
detailed in comments.

## Database
===========
The database schema is detailed in db_schema.sql. This schema is expected to be
available as a database by the name of 'labassist' accessible on 'localhost' on
the default port for Postgres with the username 'labassist' and password
'labassist'.

The main convention that needs to be handled is the way tables are named. Tables
are always given plural names, and join tables are given undescored names where
the 'parent' table is first and singular, followed by the 'child' as a plural.
The exception to this is things named after verbs.

## Web application
The main thing about the web application is how it is organized. Each page has
two PHP pages, a regular one in the main folder, and a logic one in the logic/
folder. For example, if the page was called 'example.php' the logic file should
be called 'example-logic.php'. Then, to access the page, 'navigation.php' needs
to be edited to put the page into the correct bits.

The page/ folder contains the scaffolding bits for the pages, and should
generally only need to be edited if there is a new sort of page that does not
fit the general appearance of all of the other pages.

## Daemons
There are two daemons that the site relies on to perform certain functions.
These are the mailer daemon and the session closer daemon. The mailer daemon
serves to send out messages that have been stored into the database to be sent
later, while the session closer daemon serves to clock out people who are still
clocked in at midnight.

The mailer daemon uses a particular format for the pending message types.
Essentially, message variables are encoded in the following format
> (<varname>: <varbody> ;)+
and then in the MBODY files the variables to insert are noted by something like
{varname}. Finally, MessageType needs to be edited to perform merging for those
types of messages.
