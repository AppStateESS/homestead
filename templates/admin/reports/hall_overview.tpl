<!-- Table with fixed header courtesy of http://home.tampabay.rr.com/bmerkey/examples/nonscroll-table-header.html -->
<style type="text/css">
div.tableContainer {
    width: 90%;     /* table width will be 99% of this*/
    height: 512px;  /* must be greater than tbody*/
    overflow: auto;
    margin: 0 auto;
    }

table {
    width: 99%;     /*100% of container produces horiz. scroll in Mozilla*/
    border: none;
    background-color: #ffffff;
    }
    
table>tbody {  /* child selector syntax which IE6 and older do not support*/
    overflow: auto; 
    height: 510px;
    overflow-x: hidden;
    }
    
thead tr    {
    position:relative; 
    top: expression(offsetParent.scrollTop); /*IE5+ only*/
    }
    
thead td, thead th {
    text-align: center;
    font-size: 14px; 
    background-color: #ffdb59;
    color: black;
    font-weight: bold;
    border-top: solid 1px #d8d8d8;
    }   
    
td  {
    color: #000;
    padding-right: 2px;
    font-size: 12px;
    text-align: right;
    border-bottom: solid 1px #d8d8d8;
    border-left: solid 1px #d8d8d8;
    }
    
table tfoot tr { /*idea of Renato Cherullo to help IE*/
      position: relative; 
      overflow-x: hidden;
      top: expression(parentNode.parentNode.offsetHeight >= offsetParent.offsetHeight ? 0 - parentNode.parentNode.offsetHeight + offsetParent.offsetHeight + offsetParent.scrollTop : 0);
      }


tfoot td    {
    text-align: center;
    font-size: 11px;
    font-weight: bold;
    background-color: papayawhip;
    color: steelblue;
    border-top: solid 1px slategray;
    }

td:last-child {padding-right: 20px;} /*prevent Mozilla scrollbar from hiding cell content*/
</style>

<style type="text/css" media="print">
div.tableContainer {overflow: visible;  }
table>tbody {overflow: visible; }
td {height: 14pt;} /*adds control for test purposes*/
thead td {font-size: 11pt;   }
tfoot td {
    text-align: center;
    font-size: 9pt;
    border-bottom: solid 1px slategray;
}

thead   {display: table-header-group;   }
tfoot   {display: table-footer-group;   }
thead th, thead td  {position: static; } 

thead tr    {position: static;} /*prevent problem if
                                   print after scrolling table*/ 
table tfoot tr {position: static;}
</style>

<h2>Building Overview for {HALL}</h2>
<table>
<thead>
    <td>Bedroom</td>
    <td>Bed</td>
    <td>Student</td>
</thead>
<!-- BEGIN floor_repeat -->
        <th><strong>Floor {FLOOR_NUMBER}</strong></th>
    <!-- BEGIN room_repeat -->
        <tr>
            <td>Room {ROOM_NUMBER}</td>
        </tr>
        <!-- BEGIN bed_repeat -->
        <tr>
            <td>Bedroom: {BED_LABEL}</td>
            <td>Bed: {BED}</td>
            <td>{LINK}</td>
        </tr>
        <!-- END bed_repeat -->
    <!-- END room_repeat -->
<!-- END floor_repeat -->
</table>
