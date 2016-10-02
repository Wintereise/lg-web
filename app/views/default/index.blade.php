<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <title>{{ Config::get('globals.companyName') }} (AS{{ Config::get('globals.asn') }}) {{ Lang::get('default.plg') }}</title>
        {{ HTML::style('assets/default.css') }}
        {{ HTML::script('assets/default.js') }}
        <!-- This 'template' is a relic of the past, and NOT modern HTML 5. Credits go out to NTT Communications / AS2914 for the original markup.
            What you see here is a cleaned up and templatized version of the same.
            todo: Build a proper template to replace this with someday.
        -->
    </head>
    <body>
        {{ Form::open(array('route' => 'process', 'method' => 'POST', 'id' => 'lg', 'name' => 'lg')) }}
        <div class="H6" style="font-weight: bold">
            {{ Lang::get('default.rtr') }}:
        </div>
        {{ Form::select('router', array_filter(Config::get('slaves.markup')), 0, array('onchange' => "changetext();")) }}
        <br />
        <br />
        <div class="H6" style="font-weight: bold">
            {{ Lang::get('default.query') }}:
        </div>
        {{ Form::select('query', Config::get('slaves.cmds'), 0, array('onchange' => "changetext();", 'id' => 'query')) }}
        <br />
        <br />

        <div class="H6">
            <b>
                <span id="fqdn">{{ Lang::get('default.fqdn') }}</span>
            </b>
            <br />
        </div>

        <table id="table-ip">
            <tr>
                <td>
                    {{ Form::radio('sourceIP', Request::getClientIp(), true, array('onclick' => "changetext()")) }}
                    {{ Lang::get('default.currentIPMarkup', array ('ip' => Request::getClientIp())) }}
                </td>
            </tr>
            <tr>
                <td>
                    {{ Form::radio('sourceIP', 'IP', false, array('onclick' => 'changetext()')) }}
                    {{ Lang::get('default.specify') }}
                    {{ Form::text('addr', null, array('disabled' => 'disabled', 'size' => '30')) }}
                </td>
            </tr>
        </table>

        <div id="myTable" style="visibility:visible">
            <table>
                <tr>
                    <td>
                        {{ Form::radio('sourceIP', 'FQDN', false, array('onclick' => 'changetext()')) }}
                        {{ Lang::get('default.specifyFQDN') }}
                    </td>
                    <td>
                        {{ Form::text('addrFQDN', null, array('disabled' => 'disabled', 'size' => 30)) }}
                    </td>
                    <td>
                        <div id="divIPv46" style="visibility:hidden">
                            {{ Form::radio('protocol', 'IPv4', true) }}
                            {{ Lang::get('default.ipv4') }}
                            {{ Form::radio('protocol', 'IPv6', false) }}
                            {{ Lang::get('default.ipv6') }}
                            <table></table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div id="myTable2" style="visibility:hidden">
            <table>
                <tr>
                    <td style="font-style: italic; color: dodgerblue">&nbsp; {{ Lang::get('default.bgpRestrictions') }}</td>
                </tr>
            </table>
        </div><br>

        <table>
            <tr>
                <td align="center" colspan="3">
                    {{ Form::submit('Submit', array('onclick' => "changetext();")) }} &nbsp; {{ Form::reset('Reset') }}
                </td>
            </tr>
        </table>
        <br />
        {{ Form::close() }}

        @if (Session::has('results'))
        <div id="results">
            <font color="003399">
                {{ Session::get('results') }}
            </font>
        @else
        <div id="results" style="visibility: hidden">
        @endif
        </div>
    </body>
</html>