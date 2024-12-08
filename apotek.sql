/*
SQLyog Professional v12.5.1 (64 bit)
MySQL - 10.4.28-MariaDB-log : Database - apotek
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`apotek` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `apotek`;

/*Table structure for table `tb_jenis_obat` */

DROP TABLE IF EXISTS `tb_jenis_obat`;

CREATE TABLE `tb_jenis_obat` (
  `Id_jenis` int(3) NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(15) NOT NULL,
  `bentuk_obat` enum('Tablet','Sirup','kapsul','bubuk','makanan') NOT NULL,
  PRIMARY KEY (`Id_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_jenis_obat` */

insert  into `tb_jenis_obat`(`Id_jenis`,`nama_jenis`,`bentuk_obat`) values 
(1,'Obat','Sirup'),
(2,'Obat','Tablet'),
(3,'Obat','kapsul'),
(4,'Suplemen','Tablet'),
(5,'Suplemen','kapsul'),
(6,'Vitamin','Tablet'),
(7,'Vitamin','kapsul'),
(8,'Vitamin','Sirup'),
(9,'Produk Bayi','bubuk'),
(10,'Produk Bayi','makanan');

/*Table structure for table `tb_obat` */

DROP TABLE IF EXISTS `tb_obat`;

CREATE TABLE `tb_obat` (
  `Id_Obat` int(3) NOT NULL AUTO_INCREMENT,
  `Nama_Obat` varchar(15) NOT NULL,
  `Stok_obat` int(5) NOT NULL,
  `Harga_satuan` int(8) NOT NULL,
  `Id_jenis` int(3) NOT NULL,
  PRIMARY KEY (`Id_Obat`),
  KEY `Id_jenis` (`Id_jenis`),
  CONSTRAINT `tb_obat_ibfk_1` FOREIGN KEY (`Id_jenis`) REFERENCES `tb_jenis_obat` (`Id_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_obat` */

insert  into `tb_obat`(`Id_Obat`,`Nama_Obat`,`Stok_obat`,`Harga_satuan`,`Id_jenis`) values 
(1,'gtw',76,10000,1),
(2,'ya',36,799,2),
(5,'',0,0,1),
(6,'mugi',140,1256,8),
(7,'bau',80,1256,4);

/*Table structure for table `tb_pegawai` */

DROP TABLE IF EXISTS `tb_pegawai`;

CREATE TABLE `tb_pegawai` (
  `Id_pegawai` int(3) NOT NULL AUTO_INCREMENT,
  `Nama_pegawai` varchar(15) NOT NULL,
  `No_tlp` varchar(15) NOT NULL,
  `email` varchar(15) NOT NULL,
  `Nip` varchar(10) NOT NULL,
  `passwd` varchar(10) NOT NULL,
  PRIMARY KEY (`Id_pegawai`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pegawai` */

insert  into `tb_pegawai`(`Id_pegawai`,`Nama_pegawai`,`No_tlp`,`email`,`Nip`,`passwd`) values 
(1,'metra','809ffff','edd@gmail.com','2305551110','admin123');

/*Table structure for table `tb_pelanggan` */

DROP TABLE IF EXISTS `tb_pelanggan`;

CREATE TABLE `tb_pelanggan` (
  `Id_pelanggan` int(4) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `email` varchar(15) NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  `alamat` varchar(10) NOT NULL,
  `password` varchar(15) NOT NULL,
  PRIMARY KEY (`Id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pelanggan` */

insert  into `tb_pelanggan`(`Id_pelanggan`,`username`,`email`,`no_tlp`,`alamat`,`password`) values 
(1,'anonim','-','-','-','user123'),
(2,'Metra Shandy','','1234567','0','1qaz2wsx'),
(3,'1qaz','','872927278710','jepang','$2y$10$ddTyeB4p');

/*Table structure for table `tb_pembelian` */

DROP TABLE IF EXISTS `tb_pembelian`;

CREATE TABLE `tb_pembelian` (
  `Id_pembelian` int(5) NOT NULL AUTO_INCREMENT,
  `tanggal_pembelian` date NOT NULL,
  `total_item` int(5) NOT NULL,
  `total_harga` int(10) NOT NULL,
  `Id_suplier` int(3) DEFAULT NULL,
  PRIMARY KEY (`Id_pembelian`),
  KEY `Id_suplier` (`Id_suplier`),
  CONSTRAINT `tb_pembelian_ibfk_2` FOREIGN KEY (`Id_suplier`) REFERENCES `tb_suplier` (`Id_suplier`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pembelian` */

insert  into `tb_pembelian`(`Id_pembelian`,`tanggal_pembelian`,`total_item`,`total_harga`,`Id_suplier`) values 
(1,'2024-11-17',90,9000,2),
(2,'2025-01-11',50,2250000,1),
(3,'2024-12-07',40,400000,1),
(4,'2024-12-07',40,400000,1);

/*Table structure for table `tb_pembelian_detail` */

DROP TABLE IF EXISTS `tb_pembelian_detail`;

CREATE TABLE `tb_pembelian_detail` (
  `Id_obat` int(3) DEFAULT NULL,
  `Id_pembelian` int(5) DEFAULT NULL,
  `tanggal_kadarluarsa` date DEFAULT NULL,
  `jumlah_item` int(5) DEFAULT NULL,
  `harga_satuan` int(10) DEFAULT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_pembelian` (`Id_pembelian`),
  CONSTRAINT `tb_pembelian_detail_ibfk_1` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_pembelian_detail_ibfk_2` FOREIGN KEY (`Id_pembelian`) REFERENCES `tb_pembelian` (`Id_pembelian`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pembelian_detail` */

insert  into `tb_pembelian_detail`(`Id_obat`,`Id_pembelian`,`tanggal_kadarluarsa`,`jumlah_item`,`harga_satuan`) values 
(1,1,'2025-05-23',45,6000),
(6,2,'2024-12-02',50,45000),
(1,3,'2024-12-14',40,10000),
(7,4,'2024-12-14',40,10000);

/*Table structure for table `tb_penjualan` */

DROP TABLE IF EXISTS `tb_penjualan`;

CREATE TABLE `tb_penjualan` (
  `Id_penjualan` int(5) NOT NULL AUTO_INCREMENT,
  `Tanggal_penjualan` date NOT NULL,
  `Total_item` int(3) NOT NULL,
  `harga_total` int(8) NOT NULL,
  `Id_pelanggan` int(3) NOT NULL,
  PRIMARY KEY (`Id_penjualan`),
  KEY `Id_pelanggan` (`Id_pelanggan`),
  CONSTRAINT `tb_penjualan_ibfk_1` FOREIGN KEY (`Id_pelanggan`) REFERENCES `tb_pelanggan` (`Id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_penjualan` */

insert  into `tb_penjualan`(`Id_penjualan`,`Tanggal_penjualan`,`Total_item`,`harga_total`,`Id_pelanggan`) values 
(1,'2024-12-01',40,60000,1),
(2,'2024-12-01',1,799,1),
(3,'2024-12-07',9,90000,1);

/*Table structure for table `tb_penjualan_detail` */

DROP TABLE IF EXISTS `tb_penjualan_detail`;

CREATE TABLE `tb_penjualan_detail` (
  `Id_obat` int(3) DEFAULT NULL,
  `Id_penjualan` int(5) DEFAULT NULL,
  `jumlah_item` int(5) DEFAULT NULL,
  `harga_satuan` int(8) DEFAULT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_penjualan` (`Id_penjualan`),
  CONSTRAINT `tb_penjualan_detail_ibfk_1` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_penjualan_detail_ibfk_2` FOREIGN KEY (`Id_penjualan`) REFERENCES `tb_penjualan` (`Id_penjualan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_penjualan_detail` */

insert  into `tb_penjualan_detail`(`Id_obat`,`Id_penjualan`,`jumlah_item`,`harga_satuan`) values 
(6,1,20,30000),
(1,1,20,30000),
(1,3,9,10000);

/*Table structure for table `tb_pesanan` */

DROP TABLE IF EXISTS `tb_pesanan`;

CREATE TABLE `tb_pesanan` (
  `Id_pesanan` varchar(50) NOT NULL,
  `tanggal_pemesanan` date NOT NULL,
  `Total_item` int(8) NOT NULL,
  `Harga_total` int(8) NOT NULL,
  `Id_pelanggan` int(4) NOT NULL,
  `status` enum('DIBATALKAN','PENDING','SELESAI') NOT NULL,
  PRIMARY KEY (`Id_pesanan`),
  KEY `Id_pelanggan` (`Id_pelanggan`),
  CONSTRAINT `tb_pesanan_ibfk_1` FOREIGN KEY (`Id_pelanggan`) REFERENCES `tb_pelanggan` (`Id_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pesanan` */

insert  into `tb_pesanan`(`Id_pesanan`,`tanggal_pemesanan`,`Total_item`,`Harga_total`,`Id_pelanggan`,`status`) values 
('3-1733625609','2024-12-08',1,10000,3,'PENDING'),
('3-1733625628','2024-12-08',1,799,3,'PENDING');

/*Table structure for table `tb_pesanan_detail` */

DROP TABLE IF EXISTS `tb_pesanan_detail`;

CREATE TABLE `tb_pesanan_detail` (
  `Id_pesanan` varchar(50) NOT NULL,
  `Id_obat` int(4) NOT NULL,
  `jumlah_item` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  KEY `Id_obat` (`Id_obat`),
  KEY `Id_pesanan` (`Id_pesanan`),
  CONSTRAINT `tb_pesanan_detail_ibfk_2` FOREIGN KEY (`Id_obat`) REFERENCES `tb_obat` (`Id_Obat`),
  CONSTRAINT `tb_pesanan_detail_ibfk_3` FOREIGN KEY (`Id_pesanan`) REFERENCES `tb_pesanan` (`Id_pesanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_pesanan_detail` */

insert  into `tb_pesanan_detail`(`Id_pesanan`,`Id_obat`,`jumlah_item`,`harga_satuan`) values 
('3-1733625609',1,1,10000),
('3-1733625628',2,1,799);

/*Table structure for table `tb_suplier` */

DROP TABLE IF EXISTS `tb_suplier`;

CREATE TABLE `tb_suplier` (
  `Id_suplier` int(3) NOT NULL AUTO_INCREMENT,
  `Nama_suplier` varchar(15) NOT NULL,
  `Alamat` varchar(15) NOT NULL,
  `email` varchar(15) NOT NULL,
  `no_tlp` varchar(15) NOT NULL,
  PRIMARY KEY (`Id_suplier`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `tb_suplier` */

insert  into `tb_suplier`(`Id_suplier`,`Nama_suplier`,`Alamat`,`email`,`no_tlp`) values 
(1,'kimia farma','denpasar','kimi@gmail.com','0987523145'),
(2,'farmasi','denpasar','far@gmail.com','098654');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
