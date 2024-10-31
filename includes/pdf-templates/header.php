<?php 
defined( 'ABSPATH' ) || exit;
?>
<html>
<head>
	<style>
		table {
			width: 100%;
		}
		table td {
			vertical-align: top;
		}
		table.product {
			border-top: 1px solid #ddd;
			border-left: 1px solid #ddd;
			border-collapse: collapse;
			margin-top: 40px;
		}

		table.product tr td,
		table.product tr th {
			border-bottom: 1px solid #ddd;
			border-right: 1px solid #ddd;
			padding: 10px;
		}

		table.product tr:nth-child(2n+1) td { 
			background-color: #F8F8F8;
		}

		table.product th {
			background-color: #f0f0f0;
		}

		.logo {
			max-width: 180px
		}
		.wrap {
			margin-top: 40px;
		}
		.wrap div {
			width: 35%;
		}

		.left_box {
			float: left;
			width: 45%;
		}

		.right_box {
			float: right;
			width: 40%;
			text-align: right;
		}

		.div1 {
			float: left;
		}

		.div2 {
			float: right;
			text-align: right;
		}

		p {
			margin: 0;
			line-height: 20px;
		}
	</style>
</head>
<body>
<?php do_action("pcafe_gfpi_pdf_invoices_header"); ?>