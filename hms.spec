%define name hms
%define phpws_dir /var/www/phpwebsite
%define install_dir %{phpws_dir}/mod/hms

Summary:   Housing Management System
Name:      %{name}
Version:   %{version}
Release:   %{release}
License:   GPL
Group:     Development/PHP
URL:       http://phpwebsite.appstate.edu
Source0:   %{name}-%{version}-%{release}.tar.bz2
Source1:   phpwebsite-latest.tar.bz2
Requires:  php >= 5.0.0, php-gd >= 5.0.0
BuildArch: noarch

%description
The Housing Management System

%prep
%setup -n hms

%post
/usr/bin/curl -L http://127.0.0.1/apc/clear

%install
mkdir -p "$RPM_BUILD_ROOT%{install_dir}"

# Clean up crap from the repo that doesn't need to be in production
rm -Rf "hms/util"
rm -f "hms/inc/shs0001.wsdl"
rm -f "hms/inc/shs0001.wsdl.testing"
rm -f "hms/build.xml"
rm -f "hms/hms.spec"

# Install HMS Cosign script to phpWebSite
mv "hms/inc/cosign.php" \
   "$RPM_BUILD_ROOT%{phpws_dir}/mod/users/scripts/hms-cosign.php"

# Install the production Banner WSDL file
mv "hms/inc/shs0001.wsdl.prod"\
   "$RPM_BUILD_ROOT%{install_dir}/inc/shs0001.wsdl"

# Install the cron job
#mkdir -p "$RPM_BUILD_ROOT/etc/cron.d"
#mv "$RPM_BUILD_ROOT%{install_dir}/inc/hms-cron"\
#   "$RPM_BUILD_ROOT/etc/cron.d/hms-cron"
rm -f "$RPM_BUILD_ROOT%{install_dir}/inc/hms-cron"

# Create directory for HMS Archived Reports
mkdir "$RPM_BUILD_ROOT%{phpws_dir}/files/hms_reports"

# Put the PDF generator in the right place
mkdir -p "$RPM_BUILD_ROOT/opt"
mv "hms/inc/wkhtmltopdf-i386"\
   "$RPM_BUILD_ROOT/opt/wkhtmltopdf-i386"

# What's left is HMS, copy it to its module directory
cp -r hms/* "$RPM_BUILD_ROOT%{install_dir}"


%clean
rm -rf "$RPM_BUILD_ROOT%{install_dir}"
rm -f "$RPM_BUILD_ROOT/phpws_dir/mod/usrs/scripts/hms-cosign.php"
rm -f "$RPM_BUILD_ROOT/etc/cron.d/hms-cron"
rmdir -f "$RPM_BUILD_ROOT%{phpws_dir}/files/hms_reports"
rm -f "$RPM_BUILD_ROOT/opt/wkhtmltopdf-i386"

%files
%defattr(-,root,root)
%{install_dir}
%{phpws_dir}/mod/users/scripts/hms-cosign.php"
%attr(-,apache,apache) %{phpws_dir}/files/hms_reports
#/etc/cron.d/hms-cron
%attr(0755,root,root) /opt/wkhtmltopdf-i386

%changelog
* Wed Oct 22 2012 Jeff Tickle <jtickle@tux.appstate.edu>
- Works better with Continuous Integration
* Fri Oct 21 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- Made the phpwebsite install more robust, including the theme
- Added Cron Job, but never tested it so it probably won't work
* Thu Jun  2 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- Added build.xml and hms.spec to the repository, prevented these files from installing
- Added some comments
* Thu Apr 21 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- New spec file for HMS, includes phpWebSite
