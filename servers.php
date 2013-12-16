<?php
/**
 * MyGameservers Plugin for MyBB
 * Copyright © 2010 MyBB Mods
 *
 * By: Lordzed
 * Website: http://320it.tk/
 * Version: 1.5
 */

define('IN_MYBB', 1); 
define('THIS_SCRIPT', 'mygameservers.php');
require_once "./global.php";
include ("./inc/plugins/mygameservers.lib.php");

$lang->load('mygameservers');
$context['page_title'] = $lang->mygameservers;
global_header();

echo "<table border='0' cellspacing='0' cellpadding='4' class='tborder'>
			<thead>
				<tr>
					<td class='thead' colspan='8'>
						<div>
							<strong>" . $lang->index_thead . "</strong><br>
							<div class='smalltext'></div>
						</div>
					</td>
				</tr>
			</thead>
			<tbody>
				<tr>
                <td class='tcat' align='center'><span class='smalltext'><strong>" . $lang->index_mod . "</strong></span></td>
					<td class='tcat' align='center'><span class='smalltext'><strong>" . $lang->index_os . "</strong></span></td>
					<td class='tcat' align='center'><span class='smalltext'><strong>" . $lang->index_vac . "</strong></span></td>
					<td class='tcat'><span class='smalltext'><strong>" . $lang->index_hostname . "</strong></span></td>
					<td class='tcat'><span class='smalltext'><strong>" . $lang->index_players . "</strong></span></td>
					<td class='tcat'><span class='smalltext'><strong>" . $lang->index_map . "</strong></span></td>
					<td class='tcat' align='center'><span class='smalltext'><strong>" . $lang->index_status . "</strong></span></td>
                    <td class='tcat' align='center'><span  class='smalltext'><strong>" . $lang->index_connect . "</strong></span></td>
				</tr>";

if (isset($Exception )) 
{
	echo Get_Class( $Exception ) . "at line" . $Exception->getLine( );
	echo htmlspecialchars( $Exception->getMessage( ) );
	echo $e->getTraceAsString();
}
else
{
	$query = $db->simple_select('mygameservers', '*');
	while($servidor = $db->fetch_array($query))
	{
		$Query = new SourceQuery( );
		
		$Info    = Array( );
		$Rules   = Array( );
		$Players = Array( );
		
		try
		{
			$Query->Connect( $servidor["ipadress"], $servidor["port"], 1, SourceQuery :: SOURCE );
			
			$Info    = $Query->GetInfo( );
			$Players = $Query->GetPlayers( );
			$Rules   = $Query->GetRules( );
		}
		catch( Exception $e )
		{
			$Exception = $e;
		}
		
		$Query->Disconnect( );
		
		echo "<tr>";
		if (Is_Array($Info))
		{
			$mod = $Info['ModDir'];
				
            if ($Info['ModDir'] == 'tf') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/tf2.png' title='TeamFortress 2'></td>";
			}
			elseif ($Info['ModDir'] == 'csgo') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/csgo.png' title='Counter-Strike : Global Offensive'></td>";
			}
		}
		else
		{
			echo "<td class='trow2' align='center'></td>";
		}
		
		if (Is_Array($Info))
		{
			if ($Info['Os'] == 'l') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/l.png' title='Linux'></td>";
			} 
			elseif ($Info['Os'] == 'w') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/w.png' title='Windows'></td>";
			}
		}
		else
		{
			echo "<td class='trow2' align='center'></td>";
		}
		
		if (Is_Array($Info))
		{		
			if ($Info['Secure']) 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/shield.png' title='VAC'></td>";
			}
			else
			{
				echo "<td class='trow2' align='center'></td>";
			}
		}
		else
		{
			echo "<td class='trow2' align='center'></td>";
		}
		
		if (Is_Array($Info)) 
		{
			echo "<td class='trow2'>" . $Info['HostName'] . "</td>";
		} 
		else 
		{
			echo "<td class='trow2'><strong>" . $lang->error_connecting . "</strong> (" . $servidor['ipadress'] . ":" . $servidor['port'] . ")</td>";
		} 

		if (Is_Array($Info)) 
		{
			echo "<td class='trow2'>" . $Info['Players'] . "/" . $Info['MaxPlayers'] . "</td>";
		}
		else
		{
			echo "<td class='trow2' align='center'></td>";
		}
		
		if (Is_Array($Info)) 
		{		
			echo "<td class='trow2'>" . $Info['Map'] . "</td>";
		}
		else
		{
			echo "<td class='trow2'></td>";
		}
		if (Is_Array($Info)) 
		{
			echo "<td class='trow2' align='center'><img src='./images/mygameservers/online.png' title='Online'></td>";
		} 
		else 
		{
			echo "<td class='trow2' align='center'><img src='./images/mygameservers/offline.png' title='Offline'></td>";
		}
        if (Is_Array($Info)) 
		{
			echo "<td class='trow2' align='center'><a href=steam://connect/" . $servidor['ipadress'] . ":" . $servidor['port'] . ">Connect</a></td>";
		} 
		else 
		{
			echo "<td class='trow2'><strong>" . $lang->error_connecting . "</strong> (" . $servidor['ipadress'] . ":" . $servidor['port'] . ")</td>";
		} 

		echo "</tr>";
	}
}
	echo "<tbody>";
	echo "</table>";

global_footer();

function global_header()
{
	global $headerinclude, $context, $header;

	echo '<html>
		<head>
		' . $headerinclude . '
		</head>
		<body>'
	;
}

function global_footer()
{
	global $footer;
	
	
	echo '</body>
</html>';
}
?>