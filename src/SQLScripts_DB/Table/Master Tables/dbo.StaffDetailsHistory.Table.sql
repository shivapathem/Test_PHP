USE [Allocate7]
GO
/****** Object:  Table [dbo].[StaffDetails]    Script Date: 25/06/2021 10:57:58 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dbo' AND  TABLE_NAME = 'StaffDetailsHistory')
BEGIN
	CREATE TABLE [dbo].[StaffDetailsHistory](
		[StaffID] [int] ,
		[IsVendor] [int] NULL,
		[EmpNumber] [int] NULL,
		[StaffNumber] [varchar](7) NULL,
		[VendorNumber] [varchar](20) NULL,
		[Title] [varchar](4) NULL,
		[Initials] [varchar](4) NULL,
		[Surname] [varchar](24) NULL,
		[Forename] [varchar](24) NULL,
		[PreferredForename] [varchar](40) NULL,
		[SecondName] [varchar](24) NULL,
		[BirthDate] [datetime] NULL,
		[IsFemale] [bit] NULL,
		[Room] [varchar](10) NULL,
		[SubLocation] [varchar](50) NULL,
		[BuildingCode] [varchar](3) NULL,
		[OfficeExtension] [varchar](14) NULL,
		[OfficeMobile] [varchar](14) NULL,
		[OfficePager] [varchar](14) NULL,
		[OfficeFax] [varchar](14) NULL,
		[AltTelephone] [varchar](40) NULL,
		[NetLogin] [varchar](30) NULL,
		[InternalEmail] [varchar](132) NULL,
		[ExternalEmail] [varchar](132) NULL,
		[JoinDate] [datetime] NULL,
		[IsLeaver] [bit] NULL,
		[LeaveDate] [datetime] NULL,
		[LeaveDateSat] [datetime] NULL,
		[Department] [tinyint] NULL,
		[Estab Code] [varchar](10) NULL,
		[Notes] [varchar](max) NULL,
		[CreatedDate] [datetime] NULL,
		[LastModDate] [datetime] NULL,
		[LastModBy] [varchar](50) NULL,
		[LastModChanges] [varchar](max) NULL,
		[ManualEntry] [bit]  NULL,
		[CriticalLevel] [tinyint] NULL,
		[CriticalFixed] [bit] NULL,
		[History] [varchar](max) NULL,
		[Archive_Last_AccPeriodID_Date] [datetime2](7) NULL,
		[IsScheduledPerson] [INT],
		[PHLLeaveAmount] [FLOAT]
	)
END
GO
