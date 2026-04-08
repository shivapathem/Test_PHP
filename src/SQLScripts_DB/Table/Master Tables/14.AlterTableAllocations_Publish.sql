USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS( SELECT 1 FROM sys.columns 
			WHERE Name  IN ('AllocationsDutyID','AllocationsSPID') AND Object_ID = Object_ID(N'[dbo].[Allocations_Publish]'))
 BEGIN

	alter table Allocations_Publish ADD AllocationsDutyID INT,
										AllocationsSPID INT,
										SigninStartTime	int,
										SigninEndTime	int,
										SigninStatus int,
										SigninINBuilding int
										
 END										
								  