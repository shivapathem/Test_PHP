USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AccPeriodDutySummary' )
 BEGIN

	create table AccPeriodDutySummary(
	APD_AccPeriodDutySummaryID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	APD_ScheduledPersonID	INT,
	APD_AccPeriodStartDate	DATE,
	APD_AccPeriodEndDate	DATE,
	APD_AccPeriodStartWeek	INT,
	APD_AccPeriodEndWeek	INT,
	APD_AccPeriodDuration	INT,
	APD_AccPeriodDays	INT,
	APD_TotalOverTimeHRS INT,
	APD_CreatedBy	INT,
	APD_CreatedDate	DATETIME,
	APD_UpdatedBy	INT,
	APD_UpdatedDate	DATETIME,
	constraint Con_AccPeriodDutySummary_SP_FK foreign key (APD_ScheduledPersonID) references UserDetails(UD_UserID),
	constraint Con_AccPeriodDutySummary_CrBy_FK FOREIGN KEY ( APD_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_AccPeriodDutySummary_UpdBy_FK FOREIGN KEY ( APD_UpdatedBy ) references UserDetails(UD_UserID)
	)


	CREATE NONCLUSTERED INDEX idx_AccPeriodDutySummary_SPID  ON  AccPeriodDutySummary
			  (APD_ScheduledPersonID) 
			  INCLUDE (APD_AccPeriodStartDate,APD_AccPeriodEndDate)
		  
 END		  
		  