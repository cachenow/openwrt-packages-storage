DIR="/etc/squid/cert"

module("luci.controller.squid", package.seeall)

function index()
	if not nixio.fs.access("/usr/sbin/squid") then
		return
	end

	entry({"admin", "services", "squid"}, cbi("squid"), _("Squid")).dependent = true
	entry({"admin", "services", "squid", "p12"}, call("download_p12")).leaf = true
	entry({"admin", "services", "squid", "pem"}, call("download_pem")).leaf = true
	entry({"admin", "services", "squid", "cer"}, call("download_cer")).leaf = true
	entry({"admin", "services", "squid", "ipinfo"}, call("ip_info")).leaf = true
	entry({"admin", "services", "squid", "dates"}, call("get_dates")).leaf = true
end

function downloader(fname, mimetype)
	local t, e
	t = nixio.open(DIR .. "/" .. fname, "r")
	luci.http.header('Content-Disposition','attachment; filename="squid-' .. fname .. '"')
	luci.http.prepare_content(mimetype)
	while true do
		e = t:read(nixio.const.buffersize)
		if (not e) or (#e == 0) then
			break
		end
		luci.http.write(e)
	end
	t:close()
	luci.http.close()
end

function download_p12()
	downloader("ca.p12", "application/x-pkcs12")
end

function download_pem()
	downloader("ca.pem", "application/x-pem-file")
end

function download_cer()
	downloader("ca.cer", "application/pkix-cert")
end

function ip_info()
	luci.http.prepare_content("application/json")
	luci.http.write( luci.sys.exec("curl ipinfo.io") )
end

function get_dates()
	local out = luci.sys.exec('openssl x509 -in ' .. DIR .. '/ca.pem -noout -dates')
	luci.http.prepare_content("application/json")
	luci.http.write_json({
		notBefore = string.match(out, "notBefore\=([^\n]*)") or "Invalid",
		notAfter = string.match(out, "notAfter\=([^\n]*)") or "Invalid"
	})
end
