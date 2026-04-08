USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsDayLastUpdRefreshPage]    Script Date: 10/07/2025 13:16:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER         PROCEDURE [dbo].[usp_get_ReadAllocationsDayLastUpdRefreshPage]
@StartDate            DATE,
@EndDate              DATE,
@SchedulingTeamId     INT,
@IsViewPage           INT,
@DutyHistoryID        INT = NULL,
@JobHistoryID         INT = NULL,
@SigninID             INT = NULL,
@SigninLastUpdte      INT = NULL



AS

BEGIN

	SET NOCOUNT ON	
    SET DATEFORMAT YMD
	
	DECLARE @vDutyHistoryID        INT
	DECLARE @vJobHistoryID         INT
	DECLARE @vSigninID             INT
	DECLARE @vSigninLastUpdte      INT
	DECLARE @vDutyLastUpdate       INT
	DECLARE @vJobLastUpdate        INT
	DECLARE @vPublishLastUpdate    INT
	
	BEGIN TRY
	
	 IF ( @IsViewPage = 1 )
		BEGIN		

		  SELECT @vPublishLastUpdate = min(DATEDIFF(SS,UpdatedDate,GETDATE() ) )
		    FROM Allocations_published_weeks AP
			INNER JOIN ( SELECT DISTINCT TD.ixYearWeek AS WeekNumber
			               FROM TimeDimension TD
						  WHERE TD.dDateTime between @StartDate AND @EndDate
						) TD ON AP.WeekNumber = TD.WeekNumber
		    WHERE AP.SchedulingTeamID = @SchedulingTeamId

			IF ( ISNULL(@vPublishLastUpdate,100) < 62 )
			BEGIN
			 THROW 51000, 'Week Published', 1;  			  
			END		
			
		   SELECT @vDutyHistoryID = MAX(AU_AllocationsUpdID),
		          @vDutyLastUpdate = DATEDIFF(SS,MAX(AU.AU_UpdatedDate),GETDATE() )
			 FROM Allocations AL 
			INNER JOIN TimeDimension TD ON AL.AL_WeekNumber=TD.ixYearWeek
			INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID =  ASP_AllocationsID
										   AND TD.ixDayInWeek = ASP_iDay
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN AllocationsUpdated AU ON AU.AU_AllocationsSPID = ASP_AllocationsSPID
			WHERE AL.AL_SchedulingTeamID = @SchedulingTeamId
			  AND AD.AD_IsEditedDutyAttention = 1
			  AND TD.dDateTime between @StartDate and @EndDate
			  AND AU.AU_AllocationsUpdID > ISNULL(@DutyHistoryID,1)
			  
			IF ( ISNULL(@vDutyLastUpdate,100) < 62 )
			BEGIN
			 THROW 51001, 'Duty Updated', 1;  			  
			END		

		   SELECT @vDutyHistoryID = MAX(AU_AllocationsUpdID),
		          @vDutyLastUpdate = DATEDIFF(SS,MAX(AU.AU_UpdatedDate),GETDATE() )
			 FROM Allocations AL 
			INNER JOIN TimeDimension TD ON AL.AL_WeekNumber=TD.ixYearWeek
			INNER JOIN AllocationsDuties AD ON AL_AllocationsID =  AD_AllocationsID
										   AND TD.ixDayInWeek = AD_iDay
			INNER JOIN AllocationsUpdated AU ON AU.AU_AllocationsDutyID = AD_AllocationsDutyID
			WHERE AL.AL_SchedulingTeamID = @SchedulingTeamId
			  AND AD.AD_IsEditedDutyAttention = 1
			  AND TD.dDateTime between @StartDate and @EndDate
			  AND AU.AU_AllocationsUpdID > ISNULL(@DutyHistoryID,1)
			  
			IF ( ISNULL(@vDutyLastUpdate,100) < 62 )
			BEGIN
			 THROW 51001, 'Duty Updated', 1;  			  
			END			
			
			
		END
	 ELSE
		BEGIN			
					
		   SELECT @vDutyHistoryID = MAX(AU_AllocationsUpdID),
		          @vDutyLastUpdate = DATEDIFF(SS,MAX(AU.AU_UpdatedDate),GETDATE() )
			 FROM Allocations AL 
			INNER JOIN TimeDimension TD ON AL.AL_WeekNumber=TD.ixYearWeek
			INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID =  ASP_AllocationsID
										   AND TD.ixDayInWeek = ASP_iDay
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN AllocationsUpdated AU ON AU.AU_AllocationsSPID = ASP_AllocationsSPID
			WHERE AL.AL_SchedulingTeamID = @SchedulingTeamId
			  AND TD.dDateTime between @StartDate and @EndDate
			  AND AU.AU_AllocationsUpdID > ISNULL(@DutyHistoryID,1)
			  
			IF ( ISNULL(@vDutyLastUpdate,100) < 62 )
			BEGIN
			 THROW 51001, 'Duty Updated', 1;  			  
			END		

		   SELECT @vDutyHistoryID = MAX(AU_AllocationsUpdID),
		          @vDutyLastUpdate = DATEDIFF(SS,MAX(AU.AU_UpdatedDate),GETDATE() )
			 FROM Allocations AL 
			INNER JOIN TimeDimension TD ON AL.AL_WeekNumber=TD.ixYearWeek
			INNER JOIN AllocationsDuties AD ON AL_AllocationsID =  AD_AllocationsID
										   AND TD.ixDayInWeek = AD_iDay
			INNER JOIN AllocationsUpdated AU ON AU.AU_AllocationsDutyID = AD_AllocationsDutyID
			WHERE AL.AL_SchedulingTeamID = @SchedulingTeamId
			  AND TD.dDateTime between @StartDate and @EndDate
			  AND AU.AU_AllocationsUpdID > ISNULL(@DutyHistoryID,1)
			  
			IF ( ISNULL(@vDutyLastUpdate,100) < 62 )
			BEGIN
			 THROW 51001, 'Duty Updated', 1;  			  
			END				  			  
					
		END
					
			SELECT ISNULL(@vDutyHistoryID,@DutyHistoryID) AS DutyHistoryID, 
		           ISNULL(@vJobHistoryID,@JobHistoryID) AS JobHistoryID,
		           ISNULL(@vSigninID,@SigninID) AS SignINID,  
		           ISNULL(@vSigninLastUpdte,@SigninLastUpdte) AS SigninLastUpdate,
				   0 AS IsRefresh		

	END TRY
				
	BEGIN CATCH
				
			SELECT ISNULL(@vDutyHistoryID,@DutyHistoryID) AS DutyHistoryID, 
		           ISNULL(@vJobHistoryID,@JobHistoryID) AS JobHistoryID,
		           ISNULL(@vSigninID,@SigninID) AS SignINID,  
		           ISNULL(@vSigninLastUpdte,@SigninLastUpdte) AS SigninLastUpdate,
				   1 AS IsRefresh
					
	END CATCH;		


END