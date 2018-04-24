Things to do
============
This file contains suggestions as for various things that need to be done around
the code base

* Remove redundant code
	
	There are several places where code was copy/pasted from various files,
	and thus some functions were duplicated. These functions could deal with
	being de-duplicated and stuffed into an library file.
	NOTE: You should be sure that the functions really are the same, because
	some have subtle differences. Depending on what those differences are,
	consider merging the functions and introducing a parameter for the
	difference.

* Convert string appends to heredocs
	
	Quite a few places in the code, there are places where long strings are
	constructed by line-by-line appends. These should generally be converted
	into heredocs, with results from functions and stuff stuck into
	variables and then interpolated.

	Also, the same thing should be done to the SQL queries, so as to make
	them easier to read/edit

* Improve comments
	
	The functions are mainly documented with what they do. They could almost
	certainly do with some more exposition on how they do it, as well as an
	explanation on parameter/return values.

* Use templating
	
	This is a far more extensive change than the rest of the suggested ones,
	but it could be done. Consider switching the project to using a
	templating library like smarty instead of the ad-hoc style we are doing
	now.
