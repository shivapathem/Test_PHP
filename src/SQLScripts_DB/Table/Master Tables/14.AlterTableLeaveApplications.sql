USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS( SELECT 1 FROM sys.columns 
			WHERE Name  IN ('LeaveTypeID','LeaveStartDateTime','LeaveEndDateTime') 
			AND Object_ID = Object_ID(N'[dbo].[LeaveApplications]'))
 BEGIN

	alter table LeaveApplications ADD LeaveTypeID INT,
									  LeaveStartDateTime DATETIME, 
									  LeaveEndDateTime DATETIME,
									  AllocationsSPID   INT,
									  ReasonsId 	INT,
									  isChecked  BIT
									  
 END									  