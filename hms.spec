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
Source:    %{name}-%{version}-%{release}.tar.bz2
Requires:  php >= 5.0.0, php-gd >= 5.0.0
Prefix:    /var/www/phpwebsite
BuildArch: noarch

%description
The Housing Management System

%prep
%setup -n %{name}-%{version}-%{release}

%post
/usr/bin/curl -L -k http://127.0.0.1/apc/clear

%install
mkdir -p "$RPM_BUILD_ROOT%{install_dir}"
cp -r * "$RPM_BUILD_ROOT%{install_dir}"

# Default Deletes for clean RPM

rm -Rf "$RPM_BUILD_ROOT%{install_dir}/docs/"
rm -Rf "$RPM_BUILD_ROOT%{install_dir}/.hg/"
rm -f "$RPM_BUILD_ROOT%{install_dir}/.hgtags"
rm -f "$RPM_BUILD_ROOT%{install_dir}/build.xml"
rm -f "$RPM_BUILD_ROOT%{install_dir}/faxmaster.spec"
rm -f "$RPM_BUILD_ROOT%{install_dir}/phpdox.xml"
rm -f "$RPM_BUILD_ROOT%{install_dir}/cache.properties"

# Clean up crap from the repo that doesn't need to be in production
rm -Rf "$RPM_BUILD_ROOT%{install_dir}/util"
rm -f "$RPM_BUILD_ROOT%{install_dir}/inc/shs0001.wsdl"
rm -f "$RPM_BUILD_ROOT%{install_dir}/inc/shs0001.wsdl.testing"
rm -f "$RPM_BUILD_ROOT%{install_dir}/build.xml"
rm -f "$RPM_BUILD_ROOT%{install_dir}/hms.spec"

# Install the production Banner WSDL file
mkdir -p "$RPM_BUILD_ROOT%{install_dir}/inc"
mv "$RPM_BUILD_ROOT%{install_dir}/inc/shs0001.wsdl.prod"\
   "$RPM_BUILD_ROOT%{install_dir}/inc/shs0001.wsdl"

# Install the cron job
#mkdir -p "$RPM_BUILD_ROOT/etc/cron.d"
#mv "$RPM_BUILD_ROOT%{install_dir}/inc/hms-cron"\
#   "$RPM_BUILD_ROOT/etc/cron.d/hms-cron"
rm -f "$RPM_BUILD_ROOT%{install_dir}/inc/hms-cron"

# Create directory for HMS Archived Reports
mkdir -p "$RPM_BUILD_ROOT%{phpws_dir}/files/hms_reports"

# Put the PDF generator in the right place
mkdir -p "$RPM_BUILD_ROOT/opt"
mv "$RPM_BUILD_ROOT%{install_dir}/inc/wkhtmltopdf-i386"\
   "$RPM_BUILD_ROOT/opt/wkhtmltopdf-i386"

%clean
rm -rf "$RPM_BUILD_ROOT%{install_dir}"
rm -f "$RPM_BUILD_ROOT/etc/cron.d/hms-cron"
rmdir "$RPM_BUILD_ROOT%{phpws_dir}/files/hms_reports"
rmdir "$RPM_BUILD_ROOT%{phpws_dir}/files"
rm -f "$RPM_BUILD_ROOT/opt/wkhtmltopdf-i386"

%files
%defattr(-,root,root)
%{install_dir}
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
