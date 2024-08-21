'use strict';
'require form';
'require view';
'require uci';
'require fs';
'require network';
'require rpc';
'require poll';
'require tools.widgets as widgets';
'require tools.mihomo as mihomo'

const convertBackends = [
    'https://api.dler.io/sub',
    'https://sub.id9.cc/sub',
    'https://sub.xeton.dev/sub',
    'http://127.0.0.1:25500/sub',
];

const convertTemplates = [
    {name: 'ACL4SSR', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR.ini'},
    {name: 'ACL4SSR_AdblockPlus', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_AdblockPlus.ini'},
    {name: 'ACL4SSR_BackCN', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_BackCN.ini'},
    {name: 'ACL4SSR_Mini', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Mini.ini'},
    {name: 'ACL4SSR_Mini_Fallback', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Mini_Fallback.ini'},
    {name: 'ACL4SSR_Mini_MultiMode', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Mini_MultiMode.ini'},
    {name: 'ACL4SSR_Mini_NoAuto', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Mini_NoAuto.ini'},
    {name: 'ACL4SSR_NoApple', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_NoApple.ini'},
    {name: 'ACL4SSR_NoAuto', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_NoAuto.ini'},
    {name: 'ACL4SSR_NoAuto_NoApple', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_NoAuto_NoApple.ini'},
    {name: 'ACL4SSR_NoAuto_NoApple_NoMicrosoft', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_NoAuto_NoApple_NoMicrosoft.ini'},
    {name: 'ACL4SSR_NoMicrosoft', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_NoMicrosoft.ini'},
    {name: 'ACL4SSR_Online', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online.ini'},
    {name: 'ACL4SSR_Online_AdblockPlus', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_AdblockPlus.ini'},
    {name: 'ACL4SSR_Online_Full', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Full.ini'},
    {name: 'ACL4SSR_Online_Full_AdblockPlus', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Full_AdblockPlus.ini'},
    {name: 'ACL4SSR_Online_Full_Google', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Full_Google.ini'},
    {name: 'ACL4SSR_Online_Full_MultiMode', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Full_MultiMode.ini'},
    {name: 'ACL4SSR_Online_Full_Netflix', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Full_Netflix.ini'},
    {name: 'ACL4SSR_Online_Full_NoAuto', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Full_NoAuto.ini'},
    {name: 'ACL4SSR_Online_Mini', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Mini.ini'},
    {name: 'ACL4SSR_Online_Mini_AdblockPlus', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Mini_AdblockPlus.ini'},
    {name: 'ACL4SSR_Online_Mini_Ai', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Mini_Ai.ini'},
    {name: 'ACL4SSR_Online_Mini_Fallback', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Mini_Fallback.ini'},
    {name: 'ACL4SSR_Online_Mini_MultiCountry', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Mini_MultiCountry.ini'},
    {name: 'ACL4SSR_Online_Mini_MultiMode', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Mini_MultiMode.ini'},
    {name: 'ACL4SSR_Online_Mini_NoAuto', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_Mini_NoAuto.ini'},
    {name: 'ACL4SSR_Online_MultiCountry', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_MultiCountry.ini'},
    {name: 'ACL4SSR_Online_NoAuto', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_NoAuto.ini'},
    {name: 'ACL4SSR_Online_NoReject', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_Online_NoReject.ini'},
    {name: 'ACL4SSR_WithChinaIp', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_WithChinaIp.ini'},
    {name: 'ACL4SSR_WithChinaIp_WithGFW', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_WithChinaIp_WithGFW.ini'},
    {name: 'ACL4SSR_WithGFW', url: 'https://raw.githubusercontent.com/ACL4SSR/ACL4SSR/master/Clash/config/ACL4SSR_WithGFW.ini'},
];

function renderStatus(running) {
    return updateStatus(E('input', { id: 'core_status', style: 'border: unset; font-style: italic; font-weight: bold;', readonly: '' }), running);
}

function updateStatus(element, running) {
    if (element) {
        element.style.color = running ? 'green' : 'red';
        element.value = running ? _('Running') : _('Not Running');
    }
    return element;
}

return view.extend({
    load: function () {
        return Promise.all([
            uci.load('mihomo'),
            mihomo.listProfiles(),
            mihomo.appVersion(),
            mihomo.coreVersion(),
            mihomo.status(),
            network.getHostHints(),
        ]);
    },
    render: function (data) {
        const subscriptions = uci.sections('mihomo', 'subscription');
        const profiles = data[1];
        const appVersion = data[2];
        const coreVersion = data[3];
        const running = data[4];
        const hosts = data[5].hosts;

        let m, s, o, so;

        m = new form.Map('mihomo', _('Mihomo'), `${_('Mihomo is a rule based proxy in Go.')} <a href="https://github.com/morytyann/OpenWrt-mihomo/wiki" target="_blank">${_('Usage')}</a>`);
    
        s = m.section(form.NamedSection, 'status', 'status', _('Status'));

        o = s.option(form.Value, '_app_version', _('App Version'));
        o.readonly = true;
        o.load = function (section_id) {
            return appVersion.trim();
        };
        o.write = function () {};

        o = s.option(form.Value, '_core_version', _('Core Version'));
        o.readonly = true;
        o.load = function (section_id) {
            return coreVersion.trim();
        };
        o.write = function () {};

        o = s.option(form.DummyValue, '_core_status', _('Core Status'));
        o.cfgvalue = function (section_id) {
            return renderStatus(running);
        };
        poll.add(function () {
            return L.resolveDefault(mihomo.status()).then(function (running) {
                updateStatus(document.getElementById('core_status'), running);
            });
        });

        o = s.option(form.Button, 'reload', '-');
        o.inputstyle = 'action';
        o.inputtitle = _('Reload');
        o.onclick = function () {
            return mihomo.reload();
        };

        o = s.option(form.Button, 'restart', '-');
        o.inputstyle = 'negative';
        o.inputtitle = _('Restart');
        o.onclick = function () {
            return mihomo.restart();
        };

        o = s.option(form.Button, 'update_dashboard', '-');
        o.inputstyle = 'positive';
        o.inputtitle = _('Update Dashboard');
        o.onclick = function () {
            return mihomo.callMihomoAPI('POST', '/upgrade/ui');
        };

        o = s.option(form.Button, 'open_dashboard', '-');
        o.inputtitle = _('Open Dashboard');
        o.onclick = function () {
            return mihomo.openDashboard();
        };

        s = m.section(form.NamedSection, 'config', 'config', _('Basic Config'));

        o = s.option(form.Flag, 'enabled', _('Enable'));
        o.rmempty = false;

        o = s.option(form.Flag, 'scheduled_restart', _('Scheduled Restart'));
        o.rmempty = false;

        o = s.option(form.Value, 'cron_expression', _('Cron Expression'));
        o.retain = true;
        o.rmempty = false;
        o.depends('scheduled_restart', '1');

        o = s.option(form.Value, 'profile', _('Choose Profile'));
        o.rmempty = false;

        for (const profile of profiles) {
            o.value('file:' + profile.name, _('File:') + profile.name);
        }

        for (const subscription of subscriptions) {
            o.value('subscription:' + subscription['.name'], _('Subscription:') + subscription.name);
        }

        o = s.option(form.FileUpload, 'upload_profile', _('Upload Profile'));
        o.root_directory = mihomo.profilesDir;

        o = s.option(form.Flag, 'mixin', _('Mixin'));
        o.rmempty = false;

        o = s.option(form.Flag, 'test_profile', _('Test Profile'));
        o.rmempty = false;

        s = m.section(form.NamedSection, 'proxy', 'proxy', _('Proxy Config'));

        s.tab('transparent_proxy', _('Transparent Proxy'));

        o = s.taboption('transparent_proxy', form.Flag, 'transparent_proxy', _('Enable'));
        o.rmempty = false;

        o = s.taboption('transparent_proxy', form.ListValue, 'transparent_proxy_mode', _('Mode'));
        o.rmempty = false;
        o.value('tproxy', _('TPROXY Mode'));
        o.value('tun', _('TUN Mode'));

        o = s.taboption('transparent_proxy', form.Flag, 'ipv4_dns_hijack', _('IPv4 DNS Hijack'));
        o.rmempty = false;

        o = s.taboption('transparent_proxy', form.Flag, 'ipv6_dns_hijack', _('IPv6 DNS Hijack'));
        o.rmempty = false;

        o = s.taboption('transparent_proxy', form.Flag, 'ipv4_proxy', _('IPv4 Proxy'));
        o.rmempty = false;

        o = s.taboption('transparent_proxy', form.Flag, 'ipv6_proxy', _('IPv6 Proxy'));
        o.rmempty = false;

        o = s.taboption('transparent_proxy', form.Flag, 'router_proxy', _('Router Proxy'));
        o.rmempty = false;

        o = s.taboption('transparent_proxy', form.Flag, 'lan_proxy', _('Lan Proxy'));
        o.rmempty = false;

        s.tab('access_control', _('Access Control'));

        o = s.taboption('access_control', form.ListValue, 'access_control_mode', _('Mode'));
        o.rmempty = false;
        o.value('all', _('All Mode'));
        o.value('allow', _('Allow Mode'));
        o.value('block', _('Block Mode'));

        o = s.taboption('access_control', form.DynamicList, 'acl_ip', 'IP');
        o.datatype = 'ipmask4';
        o.retain = true;
        o.depends('access_control_mode', 'allow');
        o.depends('access_control_mode', 'block');

        for (const mac in hosts) {
            const host = hosts[mac];
            for (const ip of host.ipaddrs) {
                const hint = host.name || mac;
                o.value(ip, hint ? '%s (%s)'.format(ip, hint) : ip);
            }
        }

        o = s.taboption('access_control', form.DynamicList, 'acl_ip6', 'IP6');
        o.datatype = 'ipmask6';
        o.retain = true;
        o.depends('access_control_mode', 'allow');
        o.depends('access_control_mode', 'block');

        for (const mac in hosts) {
            const host = hosts[mac];
            for (const ip of host.ip6addrs) {
                const hint = host.name || mac;
                o.value(ip, hint ? '%s (%s)'.format(ip, hint) : ip);
            }
        }

        o = s.taboption('access_control', form.DynamicList, 'acl_mac', 'MAC');
        o.datatype = 'macaddr';
        o.retain = true;
        o.depends('access_control_mode', 'allow');
        o.depends('access_control_mode', 'block');

        for (const mac in hosts) {
            const host = hosts[mac];
            const hint = host.name || host.ipaddrs[0];
            o.value(mac, hint ? '%s (%s)'.format(mac, hint) : mac);
        }

        s.tab('bypass', _('Bypass'));

        o = s.taboption('bypass', form.Flag, 'bypass_china_mainland_ip', _('Bypass China Mainland IP'));
        o.rmempty = false;

        o = s.taboption('bypass', form.Value, 'acl_tcp_dport', _('Destination TCP Port to Proxy'));
        o.rmempty = false;
        o.value('1-65535', _('All Port'));
        o.value('21 22 80 110 143 194 443 465 993 995 8080 8443', _('Commonly Used Port'));

        o = s.taboption('bypass', form.Value, 'acl_udp_dport', _('Destination UDP Port to Proxy'));
        o.rmempty = false;
        o.value('1-65535', _('All Port'));
        o.value('123 443 8443', _('Commonly Used Port'));

        o = s.taboption('bypass', widgets.NetworkSelect, 'wan_interfaces', _('WAN Interfaces'));
        o.multiple = true;
        o.optional = false;
        o.rmempty = false;

        o = s.taboption('bypass', widgets.NetworkSelect, 'wan6_interfaces', _('WAN6 Interfaces'));
        o.multiple = true;
        o.optional = true;
        o.rmempty = false;

        s = m.section(form.GridSection, 'subscription', _('Subscription Config'));
        s.addremove = true;
        s.anonymous = true;

        s.tab('subscription', _('Subscription Config'));

        o = s.taboption('subscription', form.Value, 'name', _('Subscription Name'));
        o.rmempty = false;
        o.width = '10%';

        o = s.taboption('subscription', form.Value, 'url', _('Subscription Url'));
        o.rmempty = false;

        o = s.taboption('subscription', form.Value, 'user_agent', _('User Agent'));
        o.default = 'mihomo';
        o.rmempty = false;
        o.modalonly = true;
        o.value('mihomo');
        o.value('clash.meta');
        o.value('clash');

        s.tab('convert', _('Convert Config'));

        o = s.taboption('convert', form.Flag, 'convert', _('Enable'));
        o.modalonly = true;
        o.rmempty = false;

        o = s.taboption('convert', form.Value, 'convert_backend', _('Backend'));
        o.modalonly = true;
        o.retain = true;
        o.rmempty = false;
        o.depends('convert', '1');

        for (const backend of convertBackends) {
            o.value(backend);
        }

        o = s.taboption('convert', form.Value, 'convert_template', _('Template'));
        o.modalonly = true;
        o.retain = true;
        o.rmempty = false;
        o.depends('convert', '1');

        for (const template of convertTemplates) {
            o.value(template.url, template.name);
        }

        o = s.taboption('convert', form.Flag, 'convert_advanced', _('Advanced Config'));
        o.modalonly = true;
        o.retain = true;
        o.rmempty = false;
        o.depends('convert', '1');

        o = s.taboption('convert', form.Value, 'convert_include', _('Include'));
        o.modalonly = true;
        o.retain = true;
        o.depends({'convert': '1', 'convert_advanced': '1'});
    
        o = s.taboption('convert', form.Value, 'convert_exclude', _('Exclude'));
        o.modalonly = true;
        o.retain = true;
        o.depends({'convert': '1', 'convert_advanced': '1'});

        o = s.taboption('convert', form.Flag, 'convert_emoji', _('Use Emoji'));
        o.modalonly = true;
        o.retain = true;
        o.rmempty = false;
        o.depends({'convert': '1', 'convert_advanced': '1'});

        o = s.taboption('convert', form.Flag, 'convert_insert_node_type', _('Insert Node Type'));
        o.modalonly = true;
        o.retain = true;
        o.rmempty = false;
        o.depends({'convert': '1', 'convert_advanced': '1'});

        s = m.section(form.NamedSection, 'mixin', 'mixin', _('Mixin Config'));

        s.tab('general', _('General Config'));

        o = s.taboption('general', form.ListValue, 'mode', _('Proxy Mode'));
        o.value('general', _('Global Mode'));
        o.value('rule', _('Rule Mode'));
        o.value('direct', _('Direct Mode'));

        o = s.taboption('general', form.ListValue, 'match_process', _('Match Process'));
        o.value('strict', _('Auto'));
        o.value('always', _('Enable'));
        o.value('off', _('Disable'));

        o = s.taboption('general', widgets.NetworkSelect, 'outbound_interface', _('Outbound Interface'));
        o.optional = true;
        o.rmempty = false;

        o = s.taboption('general', form.Flag, 'unify_delay', _('Unify Delay'));
        o.rmempty = false;

        o = s.taboption('general', form.Flag, 'tcp_concurrent', _('TCP Concurrent'));
        o.rmempty = false;

        o = s.taboption('general', form.Value, 'tcp_keep_alive_interval', _('TCP Keep Alive Interval'));
        o.datatype = 'integer';
        o.placeholder = '600';

        o = s.taboption('general', form.ListValue, 'log_level', _('Log Level'));
        o.value('silent');
        o.value('error');
        o.value('warning');
        o.value('info');
        o.value('debug');

        s.tab('external_control', _('External Control Config'));

        o = s.taboption('external_control', form.Value, 'ui_name', _('UI Name'));
        o.rmempty = false;

        o = s.taboption('external_control', form.Value, 'ui_url', _('UI Url'));
        o.rmempty = false;

        o = s.taboption('external_control', form.Value, 'api_port', _('API Port'));
        o.datatype = 'port';
        o.placeholder = '9090';

        o = s.taboption('external_control', form.Value, 'api_secret', _('API Secret'));
        o.rmempty = false;

        o = s.taboption('external_control', form.Flag, 'selection_cache', _('Save Proxy Selection'));
        o.rmempty = false;

        s.tab('inbound', _('Inbound Config'));

        o = s.taboption('inbound', form.Flag, 'allow_lan', _('Allow Lan'));
        o.rmempty = false;

        o = s.taboption('inbound', form.Value, 'http_port', _('HTTP Port'));
        o.datatype = 'port';
        o.placeholder = '8080';

        o = s.taboption('inbound', form.Value, 'socks_port', _('SOCKS Port'));
        o.datatype = 'port';
        o.placeholder = '1080';

        o = s.taboption('inbound', form.Value, 'mixed_port', _('Mixed Port'));
        o.datatype = 'port';
        o.placeholder = '7890';

        o = s.taboption('inbound', form.Value, 'redir_port', _('Redirect Port'));
        o.datatype = 'port';
        o.placeholder = '7891';

        o = s.taboption('inbound', form.Value, 'tproxy_port', _('TPROXY Port'));
        o.datatype = 'port';
        o.placeholder = '7892';

        o = s.taboption('inbound', form.Flag, 'authentication', _('Authentication'));
        o.rmempty = false;

        o = s.taboption('inbound', form.SectionValue, '_authentications', form.TableSection, 'authentication', _('Edit Authentications'));
        o.retain = true;
        o.depends('authentication', '1');

        o.subsection.anonymous = true;
        o.subsection.addremove = true;

        so = o.subsection.option(form.Flag, 'enabled', _('Enable'));
        so.rmempty = false;

        so = o.subsection.option(form.Value, 'username', _('Username'));
        so.rmempty = false;

        so = o.subsection.option(form.Value, 'password', _('Password'));
        so.rmempty = false;

        s.tab('tun', _('TUN Config'));

        o = s.taboption('tun', form.ListValue, 'tun_stack', _('Stack'));
        o.value('system', 'System');
        o.value('gvisor', 'gVisor');
        o.value('mixed', 'Mixed');

        o = s.taboption('tun', form.Value, 'tun_mtu', _('MTU'));
        o.placeholder = '9000';

        o = s.taboption('tun', form.Flag, 'tun_gso', _('GSO'));
        o.rmempty = false;

        o = s.taboption('tun', form.Value, 'tun_gso_max_size', _('GSO Max Size'));
        o.placeholder = '65536';
        o.depends('tun_gso', '1');

        o = s.taboption('tun', form.Flag, 'tun_endpoint_independent_nat', _('Endpoint Independent NAT'));
        o.rmempty = false;

        s.tab('dns', _('DNS Config'));

        o = s.taboption('dns', form.Value, 'dns_port', _('DNS Port'));
        o.datatype = 'port';
        o.placeholder = '1053';

        o = s.taboption('dns', form.ListValue, 'dns_mode', _('DNS Mode'));
        o.value('fake-ip', 'Fake-IP');
        o.value('redir-host', 'Redir-Host');

        o = s.taboption('dns', form.Value, 'fake_ip_range', _('Fake-IP Range'));
        o.datatype = 'cidr4';
        o.placeholder = '198.18.0.1/16';
        o.retain = true;
        o.depends('dns_mode', 'fake-ip');

        o = s.taboption('dns', form.Flag, 'fake_ip_filter', _('Overwrite Fake-IP Filter'));
        o.retain = true;
        o.rmempty = false;
        o.depends('dns_mode', 'fake-ip');

        o = s.taboption('dns', form.DynamicList, 'fake_ip_filters', _('Edit Fake-IP Filters'));
        o.retain = true;
        o.depends({ 'dns_mode': 'fake-ip', 'fake_ip_filter': '1' });

        o = s.taboption('dns', form.Flag, 'fake_ip_cache', _('Fake-IP Cache'));
        o.retain = true;
        o.rmempty = false;
        o.depends('dns_mode', 'fake-ip');

        o = s.taboption('dns', form.Flag, 'dns_ipv6', _('IPv6'));
        o.rmempty = false;

        o = s.taboption('dns', form.Flag, 'dns_system_hosts', _('Use System Hosts'));
        o.rmempty = false;

        o = s.taboption('dns', form.Flag, 'dns_hosts', _('Use Hosts'));
        o.rmempty = false;

        o = s.taboption('dns', form.Flag, 'hosts', _('Overwrite Hosts'));
        o.rmempty = false;

        o = s.taboption('dns', form.SectionValue, '_hosts', form.TableSection, 'host', _('Edit Hosts'));
        o.retain = true;
        o.depends('hosts', '1');

        o.subsection.anonymous = true;
        o.subsection.addremove = true;

        so = o.subsection.option(form.Flag, 'enabled', _('Enable'));
        so.rmempty = false;

        so = o.subsection.option(form.Value, 'domain_name', _('Domain Name'));
        so.rmempty = false;

        so = o.subsection.option(form.DynamicList, 'ip', _('IP'));

        o = s.taboption('dns', form.Flag, 'dns_nameserver', _('Overwrite Nameserver'));
        o.rmempty = false;

        o = s.taboption('dns', form.SectionValue, '_dns_nameserver', form.TableSection, 'nameserver', _('Edit Nameservers'));
        o.retain = true;
        o.depends('dns_nameserver', '1');

        o.subsection.anonymous = true;
        o.subsection.addremove = false;

        so = o.subsection.option(form.Flag, 'enabled', _('Enable'));
        so.rmempty = false;

        so = o.subsection.option(form.ListValue, 'type', _('Type'));
        so.value('default-nameserver');
        so.value('proxy-server-nameserver');
        so.value('nameserver');
        so.value('fallback');
        so.readonly = true;

        so = o.subsection.option(form.DynamicList, 'nameserver', _('Nameserver'));

        o = s.taboption('dns', form.Flag, 'dns_fallback_filter', _('Overwrite Fallback Filter'));
        o.rmempty = false;

        o = s.taboption('dns', form.SectionValue, '_dns_fallback_filters', form.TableSection, 'fallback_filter', _('Edit Fallback Filters'));
        o.retain = true;
        o.depends('dns_fallback_filter', '1');

        o.subsection.anonymous = true;
        o.subsection.addremove = false;

        so = o.subsection.option(form.Flag, 'enabled', _('Enable'));
        so.rmempty = false;

        so = o.subsection.option(form.ListValue, 'type', _('Type'));
        so.value('geoip-code', _('GeoIP'));
        so.value('geosite', _('GeoSite'));
        so.value('ipcidr', _('IPCIDR'));
        so.value('domain_name', _('Domain Name'));
        so.readonly = true;

        so = o.subsection.option(form.DynamicList, 'value', _('Value'));

        o = s.taboption('dns', form.Flag, 'dns_nameserver_policy', _('Overwrite Nameserver Policy'));
        o.rmempty = false;

        o = s.taboption('dns', form.SectionValue, '_dns_nameserver_policies', form.TableSection, 'nameserver_policy', _('Edit Nameserver Policies'));
        o.retain = true;
        o.depends('dns_nameserver_policy', '1');

        o.subsection.anonymous = true;
        o.subsection.addremove = true;

        so = o.subsection.option(form.Flag, 'enabled', _('Enable'));
        so.rmempty = false;

        so = o.subsection.option(form.Value, 'matcher', _('Matcher'));
        so.rmempty = false;

        so = o.subsection.option(form.DynamicList, 'nameserver', _('Nameserver'));

        s.tab('sniffer', _('Sniffer Config'));

        o = s.taboption('sniffer', form.Flag, 'sniffer', _('Enable'));
        o.rmempty = false;

        o = s.taboption('sniffer', form.Flag, 'sniff_dns_mapping', _('Sniff Redir-Host'));
        o.rmempty = false;

        o = s.taboption('sniffer', form.Flag, 'sniff_pure_ip', _('Sniff Pure IP'));
        o.rmempty = false;

        o = s.taboption('sniffer', form.Flag, 'sniffer_overwrite_dest', _('Overwrite Destination'));
        o.rmempty = false;

        o = s.taboption('sniffer', form.DynamicList, 'sniffer_force_domain_name', _('Force Sniff Domain Name'));

        o = s.taboption('sniffer', form.DynamicList, 'sniffer_ignore_domain_name', _('Ignore Sniff Domain Name'));

        o = s.taboption('sniffer', form.SectionValue, '_sniffer_sniffs', form.TableSection, 'sniff', _('Sniff By Protocol'));

        o.subsection.anonymous = true;
        o.subsection.addremove = false;

        so = o.subsection.option(form.Flag, 'enabled', _('Enable'));
        so.rmempty = false;

        so = o.subsection.option(form.ListValue, 'protocol', _('Protocol'));
        so.value('HTTP');
        so.value('TLS');
        so.value('QUIC');
        so.readonly = true;

        so = o.subsection.option(form.DynamicList, 'port', _('Port'));
        so.datatype = 'portrange';

        so = o.subsection.option(form.Flag, 'overwrite_dest', _('Overwrite Destination'));
        so.rmempty = false;

        s.tab('geox', _('GeoX Config'));

        o = s.taboption('geox', form.ListValue, 'geoip_format', _('GeoIP Format'));
        o.value('dat');
        o.value('mmdb');

        o = s.taboption('geox', form.ListValue, 'geodata_loader', _('GeoData Loader'));
        o.value('standard', _('Standard Loader'));
        o.value('memconservative', _('Memory Conservative Loader'));

        o = s.taboption('geox', form.Value, 'geosite_url', _('GeoSite Url'));
        o.rmempty = false;

        o = s.taboption('geox', form.Value, 'geoip_mmdb_url', _('GeoIP(MMDB) Url'));
        o.rmempty = false;

        o = s.taboption('geox', form.Value, 'geoip_dat_url', _('GeoIP(DAT) Url'));
        o.rmempty = false;

        o = s.taboption('geox', form.Value, 'geoip_asn_url', _('GeoIP(ASN) Url'));
        o.rmempty = false;

        o = s.taboption('geox', form.Flag, 'geox_auto_update', _('GeoX Auto Update'));
        o.rmempty = false;

        o = s.taboption('geox', form.Value, 'geox_update_interval', _('GeoX Update Interval'), _('Hour'));
        o.datatype = 'integer';
        o.placeholder = '24';
        o.retain = true;
        o.depends('geox_auto_update', '1');

        s.tab('mixin_file_content', _('Mixin File Content'), _('Please go to the editor tab to edit the file for mixin'));

        o = s.taboption('mixin_file_content', form.HiddenValue, '_mixin_file_content');

        return m.render();
    }
});
