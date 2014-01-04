<?php
/**
 * MyGameservers Plugin for MyBB
 * Copyright © 2010 MyBB Mods
 *
 * By: Lordzed
 * Website: http://320it.tk/
 * Version: 1.9
 */

define('IN_MYBB', 1); 
define('THIS_SCRIPT', 'mygameservers.php');
require_once "./global.php";
require_once ("./inc/plugins/mygameservers.lib.php");

$lang->load('mygameservers');
$context['page_title'] = $lang->mygameservers;
global_header();

// css style

echo "
<script type='text/javascript' src='jscripts/jquery-1.7.2.min.js'></script>
<script type='text/javascript'>
	jQuery.noConflict();
	jQuery(document).ready(function($){
		$('.mygameservers_opener').click(function(){
			$(this).next().fadeToggle();
		});
	});
</script>
<style type='text/css'>
	.mygameservers_hidden {
		display: none;
	}
	.mygameservers_opener:hover {
		background: #F7F7F7;
		cursor: pointer;
	}
	.mygameservers_opener {
		-webkit-transition: all 250ms ease-in-out;
		-moz-transition: all 250ms ease-in-out;
		-o-transition: all 250ms ease-in-out;
		transition: all 250ms ease-in-out;
	}
	.mygameservers_td {
		font-size: 10px;
		padding: 5px;
	}
</style>
";


echo "<table border='0' cellspacing='0' cellpadding='4' class='tborder'>
			<thead>
				<tr>
					<td class='thead' colspan='9'>
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
                    <td class='tcat' align='center'><span class='smalltext'><strong>" . $lang->index_gametracker . "</strong></span></td>
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
		
		echo "<tr class='mygameservers_opener'>";
		if (Is_Array($Info))
		{
			$mod = $Info['ModDir'];
			// Games that are supported	
            if ($Info['ModDir'] == 'tf') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/tf2.png' title='TeamFortress 2'></td>";
			}
			elseif ($Info['ModDir'] == 'csgo') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/csgo.png' title='Counter-Strike : Global Offensive'></td>";
			}
            elseif ($Info['ModDir'] == 'dota') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/dota.png' title='Dota 2'></td>";   
			}
            elseif ($Info['ModDir'] == 'left4dead') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/l4d.png' title='Left 4 Dead '></td>";   
			}
              elseif ($Info['ModDir'] == 'left4dead2') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/l4d2.png' title='Left 4 Dead 2'></td>";   
			}
            elseif ($Info['ModDir'] == 'garrysmod') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/gmod.png' title='Garry`s Mod'></td>";   
			}
            elseif ($Info['ModDir'] == 'alienswarm') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/alienswarm.png' title='Alienswarm'></td>";   
			}
            elseif ($Info['ModDir'] == 'dod') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/dods.png' title='Day of Defeat : Source'></td>";   
			}
            elseif ($Info['ModDir'] == 'hl2mp') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/hl2dm.png' title='Half-Life 2 Deathmatch'></td>";   
			}
            elseif ($Info['ModDir'] == 'cstrike') 
			{
				echo "<td class='trow2' align='center'><img src='./images/mygameservers/css.png' title='Counter-Strike Source'></td>";   
			}     
		}
		else
		{
			echo "<td class='trow2' align='center'></td>";
		}
		
        
        // info if the server runs on linux or windows
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
		// info if the server is vac or not
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
        // Game Tracker link for the servers
        if (Is_Array($Info)) 
		{
			echo "<td class='trow2' align='center'><a href=http://www.gametracker.com/server_info/" . $servidor['ipadress'] . ":" . $servidor['port'] . "><img src='./images/mygameservers/gametracker.gif'></a></td>";
		} 
		else 
		{
			echo "<td class='trow2'><strong>" . $lang->error_connecting . "</strong> (" . $servidor['ipadress'] . ":" . $servidor['port'] . ")</td>";
		} 
		if (Is_Array($Info)) 
		{
			echo "<td class='trow2'>" . $Info['HostName'] . "</td>";
		} 
		else 
		{
			echo "<td class='trow2'><strong>" . $lang->error_connecting . "</strong> (" . $servidor['ipadress'] . ":" . $servidor['port'] . ")</td>";
		} 
        // number of players that are on the server
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
        
        // online status
		if (Is_Array($Info)) 
		{
			echo "<td class='trow2' align='center'><img src='./images/mygameservers/online.png' title='Online'></td>";
		} 
		else 
		{
			echo "<td class='trow2' align='center'><img src='./images/mygameservers/offline.png' title='Offline'></td>";
		}
        
        // connect to the servers
        if (Is_Array($Info)) 
		{
			echo "<td class='trow2' align='center'><a href=steam://connect/" . $servidor['ipadress'] . ":" . $servidor['port'] . "><img src='./images/mygameservers/join.png'></a></td>";
		} 
		else 
		{
			echo "<td class='trow2'><strong>" . $lang->error_connecting . "</strong> (" . $servidor['ipadress'] . ":" . $servidor['port'] . ")</td>";
		} 

 // Show Players
echo "<tr class='mygameservers_hidden'><td colspan='7'>";
echo "<table border='0' cellspacing='0' cellpadding='4' class='tborder' style='width: 48%; float: left;'>
				<thead>
					<td class='thead' colspan='3'>
						<strong>" . $lang->mygameservers_players . "</strong>
					</td>
				</thead>
				<tbody>
					<tr>
						<td class='tcat'>" . $lang->mygameservers_players_name . "</td>
						<td class='tcat'>" . $lang->mygameservers_players_score . "</td>
						<td class='tcat'>" . $lang->mygameservers_players_time . "</td>
					</tr>";
		if (Is_Array($Players))
		{
			foreach ($Players as $Player)
			{		
				echo "<tr>";
				echo "<td class='trow2 mygameservers_td'>" . htmlspecialchars($Player["Name"]) . "</td>";
				echo "<td class='trow2 mygameservers_td'>" . htmlspecialchars($Player["Frags"]) . "</td>";
				echo "<td class='trow2 mygameservers_td'>" . htmlspecialchars($Player["TimeF"]) . "</td>";
				echo "</tr>";
			}
		}
		else
		{
			echo "<tr>";
			echo "<td class='trow2 mygameservers_td'>" . $lang->mygameservers_players_not_found . "</td>";
			echo "</tr>";
		}
		echo "</tbody></table>";
		if ($mybb->settings['mygs_show_rules'])
		{
			echo "<table border='0' cellspacing='0' cellpadding='4' class='tborder' style='width: 48%;'>
					<thead>
						<td class='thead' colspan='2'>
							<strong>" . $lang->mygameservers_rules . "</strong>
						</td>
					</thead>
					<tbody>
						<tr>
							<td class='tcat'>" . $lang->mygameservers_rules_name . "</td>
							<td class='tcat'>" . $lang->mygameservers_rules_value . "</td>
						</tr>";
			if (Is_Array($Rules))
			{
				foreach ($Rules as $Rule => $Value)
				{		
					echo "<tr>";
					echo "<td class='trow2 mygameservers_td'>" . htmlspecialchars($Rule) . "</td>";
					echo "<td class='trow2 mygameservers_td'>" . htmlspecialchars($Value) . "</td>";
					echo "</tr>";
				}
			}
			else
			{
				echo "<tr>";
				echo "<td class='trow2 mygameservers_td'>" . $lang->mygameservers_rules_not_found . "</td>";
				echo "</tr>";
			}
			echo "</tbody></table>";
		}
		echo "</td></tr>";
	}
}
	echo "<tbody>";
	echo "</table>";

global_footer();

function global_header()
{
	global $headerinclude, $context, $header;
}

function global_footer()
{
	global $footer;
	
	
	echo '</body>
</html>';
}
?>
