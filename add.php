<?php
echo 'this is test';
			include('CutPeice.php');
			include('Classes/Result.php');
			include('config.php');
            	session_start();
			function createCutpeice($type,$width,$height,$quantity,$sheet,$documentIndex){
				$cutPeice = new CutPeice();
				$cutPeice->setType($type);
				$cutPeice->setWidth($width);
				$cutPeice->setHeight($height);
				$cutPeice->setQuantity($quantity);
				$cutPeice->setSheet($sheet);
				$cutPeice->setDocumentIndex($documentIndex);
				//$kitchen = $_SESSION['kitchen'];
				
				return $cutPeice;
			}
				$width = $_POST['width'];
				$height = $_POST['height'];
				$depth = $_POST['depth'];
				$unit = $_POST['unit'];
				$option = $_POST['option'];
				$category = $_POST['category'];
				$shutters = $_POST['shutters'];
                $expose = $_POST['expose'];
				$exposeSheet = $_POST['exposeColor'];
				$shutterSheet = $_POST['shutterColour'];
                $total = $_POST['total'];
				$CutPeices = $_SESSION['cutPeices'];
				
				$documents = $_SESSION['documents'];
				$documentIndex = count($documents);
				
				$query="SELECT c.name cName, c.standard_width,c.standard_height,c.width,c.width_loss,c.height,c.height_loss,c.sheet,c.is_edge_band,c.quantity,o.option_name,o.add_ons,ct.name,u.short_name,u.edge_band,u.legs,u.hinges FROM cutlist c, options o, units u, category ct where c.option_id=o.id and o.unit_id=u.id and o.category_id=ct.id and ct.unit_id=u.id and c.option_id=".$option;	
//echo $query;				
				$result_set=mysqli_query($con,$query);
				//$result = mysqli_fetch_all($result_set,MYSQLI_ASSOC);
				$result = array();
				while ($row = $result_set->fetch_assoc()) {
					$result[] = $row;
				}				
                 $s_edge_band =0;
$exposeDoc = "";
				foreach ($result as $row)
				{
					$cWidth = $row['standard_width'];
					$widthLoss = $row['width_loss'];
					if(is_null($cWidth)){
						$cWidth = $row['width'];
						$cWidth = $_POST[''.$cWidth];
						
						
					}
					$cHeight = $row['standard_height'];
					if(is_null($cHeight)){
						$cHeight = $row['height'];
						$cHeight = $_POST[''.$cHeight];
						$heightLoss = $row['height_loss'];
						$cHeight = $cHeight-$heightLoss;
					}
					$sheet = $row['sheet'];
					$is_edge_band = $row['is_edge_band'];
					$edge_band = $row['edge_band'];
					$legs = $row['legs'];
					$hinges = $row['hinges'];
					$short_name = $row['short_name'];
				$cName = $row['cName'];	
                if($cName=='Shutters'){
					$s_edge_band=(($width+$height)*2)/1000;
						if($shutters==2){
							$cWidth = $cWidth/2;
							//$cHeight = $cHeight/2;
							$quantity = $shutters;
                            $s_edge_band = $s_edge_band*2;
						}else{
							
							$quantity = $row['quantity'];
						}
					}else{
                        
                        $quantity = $row['quantity'];
						if($quantity==0){
							$quantity = $_POST['quantity'];
						}
                        
					}
                    
                    if($is_edge_band){
                        $cWidth = $cWidth-($edge_band*2);
				        $cHeight = $cHeight-($edge_band*2);
                    }
					
                    $exposeQuantity = 0;
					$cWidth = $cWidth-$widthLoss;
					
					if($expose=='5'){
						$exposeDoc = "with All exposed";
						if($cName=='Shutters')
							$CutPeices[$shutterSheet][] = createCutpeice($short_name,$cWidth,$cHeight,$quantity*$total,$shutterSheet,$documentIndex);
						else
							$CutPeices[$exposeSheet][] = createCutpeice($short_name,$cWidth,$cHeight,$quantity*$total,$exposeSheet,$documentIndex);
					}else{
                    
                    if($cName=='side panel'){
                        $exposeDoc = "";
                        switch ($expose) {
                                case "2":
                                    $quantity = $quantity-1;
                                    $exposeQuantity = 1;
                                    $exposeDoc = "with LHS exposed";
                                    break;
                                case "3":
                                    $quantity = $quantity-1;
                                    $exposeQuantity = 1;
                                    $exposeDoc = "with RHS exposed";
                                    break;
                                case "4":
                                    $quantity = $quantity-2;
                                    $exposeQuantity = 2; 
                                    $exposeDoc = "with LHS & RHS exposed";
                                    break;
                            }
                    }

                    if($quantity>0){
						if($cName=='Shutters')
							$CutPeices[$shutterSheet][] = createCutpeice($short_name,$cWidth,$cHeight,$quantity*$total,$shutterSheet,$documentIndex);
						elseif($sheet=='C')
							$CutPeices[$exposeSheet][] = createCutpeice($short_name,$cWidth,$cHeight,$quantity*$total,$exposeSheet,$documentIndex);
						else
							$CutPeices[$sheet][] = createCutpeice($short_name,$cWidth,$cHeight,$quantity*$total,$sheet,$documentIndex);
                    }
                    if($exposeQuantity>0){
						if($option=='58')
							$CutPeices[$exposeSheet][] = createCutpeice($short_name,$cWidth-2,$cHeight,$exposeQuantity*$total,$exposeSheet,$documentIndex);
						else
							$CutPeices[$exposeSheet][] = createCutpeice($short_name,$cWidth-2,$cHeight-2,$exposeQuantity*$total,$exposeSheet,$documentIndex);
                    }
					}
                                        					
				}
                $c_edge_band=((($height+$depth)*2)+($width*2))/1000;
				$need_addons = $row['add_ons'];
				if($need_addons){
					$CutPeices['addons'][] = array('legs'=>$legs,'hinges'=>$hinges,'s_edge_band'=>$s_edge_band,'c_edge_band'=>$c_edge_band) ;
				}
                 

               

				 $_SESSION['cutPeices'] = $CutPeices;
                $uName = $row['name'];
                $optionName = $row['option_name'];
                $optionName=preg_replace('/_+/', " ".$quantity." ", $optionName);
                $documents[$documentIndex]=$total." ".$uName." ".$optionName.$exposeDoc." ".$width." width ";
                $_SESSION['documents'] = $documents;
				 $resultObj = new Result();
				$resultObj->getCutlistTable($CutPeices);
?>
<div class="row" id="divToPrint">
    <div class="col-lg-12">
        <ul>
           <?php
                foreach ($documents as $key=>$document)
                    {
                    echo "<li>$document  <a href='#' class='removeDocument' value='$key'>X</a></li>";
                }
            ?>
        </ul>
    </div>
</div>
			 <div class="row cBtn lb text-right">
			 <ul>
                <li class="buy">
					<a href="save.php"><i class="fa fa-arrow-down"></i>Save</a>
				 </li>
                 <!--
				 <li class="buy">
					<a href="download.php"><i class="fa fa-arrow-down"></i>Download</a>
				 </li>
                 -->
                 <li class="buy">
                 <input type="button" value="print" onclick="PrintDiv();" />
                 </li>
                 <script type="text/javascript">     
    function PrintDiv() {    
       var divToPrint = document.getElementById('divToPrint');
       var popupWin = window.open('', '_blank', 'width=300,height=300');
       popupWin.document.open();
       popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
        popupWin.document.close();
            }
 </script>
			 </ul>
			 </div>
			
