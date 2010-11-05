=========================================================================================
Additional notes on the becookies extension // 2010-11-05 Oliver Hader <oliver@typo3.org>
=========================================================================================

This extension requires at least TYPO3 4.4.0 to have it working out of the box.

But, since there have been requests to have it also working with version below
4.4.0, e.g. 4.3.0 or 4.2.0, it's required to manually patch the TYPO3 Core source.
Find the accordant patch files to be applied in the "compatibility" directory if
this extension. There you'll find the following files:

	* 10869.patch, see http://bugs.typo3.org/view.php?id=10869 for details
	* 14383.patch, see http://bugs.typo3.org/view.php?id=14383 for details

=========================================================================================
    ATTENTION: It is not recommended to patch TYPO3 Core sources since this might
    prevent to possibility of upgrading to newer versions and keep the manually
    installed features. If you still modify your copy of a TYPO3 release, pleases
    consider that your changes are documented well on your side and that you know
    what you're doing...
=========================================================================================
