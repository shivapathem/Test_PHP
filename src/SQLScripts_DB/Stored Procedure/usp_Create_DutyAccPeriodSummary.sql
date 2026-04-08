USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Create_DutyAccPeriodSummary]    Script Date: 02/01/2026 16:39:08 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER               PROCEDURE  [dbo].[usp_Create_DutyAccPeriodSummary]
@pStartWeek			INT,
@pEndWeek			INT,
@pTeamId			INT,
@pNetLogin          VARCHAR(30) 
AS

BEGIN

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;
    
   DECLARE    @vuserID        INT,
			  @StartWeek      INT,
			  @EndWeek        INT;

   DECLARE @TempAccounting TABLE (schedulingpersonid INT,
								   startdate DATE,
								   enddate DATE,
								   startweek INT,
								   endweek INT,
								   totalduration INT,
								   OverTimeHrs INT,
								   NoOfDays INT);

   DECLARE @TempAcctMissing TABLE (SchedulingTeamID INT, 
								   weeknumber INT, 
								   ScheduledPersonID INT,
								   totalduration INT,
								   OverTimeHrs INT,
								   NoOfDays INT);
  
   select @vuserID = UD_UserID 
     from UserDetails
    where UD_NetLogin = @pNetLogin  
  
     BEGIN TRY
        BEGIN TRANSACTION  

		INSERT INTO @TempAccounting
		SELECT distinct sp.UD_UserID,
			   AGD.AccPeriodStartDate as startdate , 
			   AGD.AccPeriodEndDate as enddate, 
			   AGD.AccPeriodStartWeek as startweek, 
			   AGD.AccPeriodEndWeek as endweek,
			   0 as totalduration,
			   0 as OverTimeHrs,
			   0 as NoOfDays
		  FROM UserDetails AS sp (nolock) 
		 INNER JOIN ScheduledPersonTeam_LINK  AS spl (nolock) ON sp.UD_UserID = spl.scheduledpersonid
		 INNER JOIN UserConfigs SCP ON  SCP.UC_UserID = sp.UD_UserID		 
		 INNER JOIN REF_AccountingGroup_Dates AGD ON SCP.UC_AccGroupID=AGD.AccGroupID
		 INNER join Timedimension TD on AGD.BBCWeek = TD.ixYearWeek 
		 WHERE TD.dDateTime between  SCP.UC_StartDate and SCP.UC_EndDate 
		   AND TD.dDateTime between spl.startdate and spl.enddate
		   AND TD.dDateTime between isnull( AGD.AccPeriodStartDate, TD.dDateTime) and isnull( AGD.AccPeriodEndDate, TD.dDateTime ) 
		   AND spl.scheduledType = 1
		   AND spl.IsHomeTeam = 1
		   AND td.ixYearWeek >= @pStartWeek AND td.ixYearWeek <= @pEndWeek
		   AND SPL.TeamID = @pTeamID	

					
					SELECT @StartWeek= MIN(startweek),
					       @EndWeek = MAX(endweek)
					  FROM @TempAccounting


			 update TC
				set TC.totalduration = AC.totaldtn,
				    TC.NoOfDays = AC.NoOfDays,
					TC.OverTimeHrs = AC.OverTimeHrs
			   from @TempAccounting TC
			  inner join 
			  (
				 select TA.schedulingpersonid,
						ta.startweek, 
						sum( case when isnull(ASP_WIADStatus,0)=1 then 0 
							  else case when AD_DutyType < 7 
										THEN isnull(AD_Duration,0)-isnull(AD_DutyBreakTime,0)
										WHEN AD_DutyType IN (8,11,12) AND ASP_LeaveStatus = 1
										THEN isnull(ASP_LeaveDuration,0)
										ELSE 0
								    END
							  end) as totaldtn,
						sum( case when isnull(ASP_WIADStatus,0) = 1 then 0 
							 ELSE case when AD_DutyType < 7 AND isnull(ASP_WIADStatus,0) = 0 AND isnull(AD_Duration,0) > 0
										THEN 1
										WHEN AD_DutyType IN (8,11,12) AND isnull(ASP_LeaveDuration,0) > 0 AND ASP_LeaveStatus = 1
										THEN 1
										ELSE 0
								    END
								end) as NoOfDays,
						sum( isnull(ASP_OverTimeHours,0)) as OverTimeHrs
				   from Allocations AL (nolock)
				  INNER JOIN TimeDimension TD (nolock) on TD.ixYearWeek = AL.AL_WeekNumber 
				  INNER JOIN AllocationsScheduledPersons ASP on  AL.AL_AllocationsID = ASP.ASP_AllocationsID
														  AND TD.dDateTime = ASP.ASP_DutyDate
				  INNER JOIN @TempAccounting TA ON TA.schedulingpersonid = ASP.ASP_SchedulingPersonID				 				  
				  INNER JOIN AllocationsDuties AD on ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
				  WHERE td.dDateTime >= ta.startdate and td.dDateTime <= ta.enddate	
				    AND AL_Status in (0,1)
				  group by TA.schedulingpersonid,ta.startweek
			  ) AC ON TC.schedulingpersonid = AC.schedulingpersonid 
				  AND TC.startweek = AC.startweek	

				 DELETE ADS
				   FROM AccPeriodDutySummary ADS
				  INNER JOIN @TempAccounting TA ON
								    ADS.APD_ScheduledPersonID = ta.schedulingpersonid
								   and ADS.APD_AccPeriodStartDate <= ta.enddate 
								   and ADS.APD_AccPeriodEndDate >= ta.startdate 

			INSERT INTO AccPeriodDutySummary (
					APD_ScheduledPersonID,
					APD_AccPeriodStartDate,
					APD_AccPeriodEndDate,
					APD_AccPeriodStartWeek,
					APD_AccPeriodEndWeek,
					APD_AccPeriodDuration,
					APD_AccPeriodDays,
					APD_TotalOverTimeHRS,
					APD_CreatedBy,
					APD_CreatedDate)
			 SELECT TA.schedulingpersonid ,
					TA.startdate,
					TA.enddate,
					TA.startweek,
					TA.endweek,
					TA.totalduration,
					TA.NoOfDays,
					TA.OverTimeHrs,
					@vuserID, 
				    getutcdate()
			  from @TempAccounting TA


		 -- Create ROTA duration Summary End


	   INSERT INTO @TempAcctMissing
	   select distinct AL.AL_SchedulingTeamID SchedulingTeamID, 
			  AL.AL_WeekNumber weeknumber, 
			  spl.ScheduledPersonID,
		   	   cast(NULL as INT) as totalduration,
			   cast(NULL as INT) as OverTimeHrs,
			   cast(NULL as INT) as NoOfDays
		  from Allocations AL			
		 INNER JOIN ScheduledPersonTeam_LINK  AS spl (nolock) ON spl.TeamID = AL.AL_SchedulingTeamID
		 INNER JOIN AllocationsScheduledPersons ASP on spl.ScheduledPersonID = ASP.ASP_SchedulingPersonID
													and ASP.ASP_AllocationsID = AL.AL_AllocationsID
		 inner join Timedimension TD on AL.AL_WeekNumber = TD.ixYearWeek and TD.dDateTime = ASP.ASP_DutyDate
		 where not exists ( select 1
							 from @TempAccounting TA
							 where TD.dDateTime >= ta.startdate and TD.dDateTime <= ta.enddate 
							   and spl.ScheduledPersonID = TA.schedulingpersonid
							 )
			and spl.scheduledType = 1
			and spl.IsHomeTeam = 1
			and TD.dDateTime >= spl.StartDate and TD.dDateTime <= spl.EndDate
		   AND td.ixYearWeek >= @pStartWeek AND td.ixYearWeek <= @pEndWeek
		    AND SPL.TeamID = @pTeamID	

			 update TC
				set TC.totalduration = AC.totaldtn,
				    TC.NoOfDays = AC.NoOfDays,
					TC.OverTimeHrs = AC.OverTimeHrs
			   from @TempAcctMissing TC
			  inner join 
			  (
				 select TA.ScheduledPersonID,
						ta.WeekNumber, 
						sum( case when isnull(ASP_WIADStatus,0)=1 then 0 
							  else case when AD_DutyType < 7 
										THEN isnull(AD_Duration,0)-isnull(AD_DutyBreakTime,0)
										WHEN AD_DutyType IN (8,11,12) AND ASP_LeaveStatus = 1
										THEN isnull(ASP_LeaveDuration,0)
										ELSE 0
								    END
							  end) as totaldtn,
						sum( case when isnull(ASP_WIADStatus,0) = 1 then 0 
							 ELSE case when AD_DutyType < 7 AND isnull(ASP_WIADStatus,0) = 0 AND isnull(AD_Duration,0) > 0
										THEN 1
										WHEN AD_DutyType IN (8,11,12) AND isnull(ASP_LeaveDuration,0) > 0 AND ASP_LeaveStatus = 1
										THEN 1
										ELSE 0
								    END
								end) as NoOfDays,
						sum( isnull(ASP_OverTimeHours,0)) as OverTimeHrs
				   from @TempAcctMissing TA
				  INNER JOIN Allocations AL (nolock) on AL.AL_SchedulingTeamID = TA.SchedulingTeamID
													AND AL.AL_WeekNumber = TA.weeknumber
				  INNER JOIN TimeDimension TD (nolock) on TD.ixYearWeek = AL.AL_WeekNumber 
				  INNER JOIN AllocationsScheduledPersons ASP on AL.AL_AllocationsID = ASP.ASP_AllocationsID
															AND TA.ScheduledPersonID = ASP.ASP_SchedulingPersonID
															and TD.ixDayInWeek = ASP.ASP_iDay
				  INNER JOIN AllocationsDuties AD on ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
				  WHERE td.ixYearWeek = TA.WeekNumber	
				    AND AL_Status in (0,1,2)
				  group by TA.ScheduledPersonID,ta.WeekNumber
			  ) AC ON TC.ScheduledPersonID = AC.ScheduledPersonID 
				  AND TC.WeekNumber = AC.WeekNumber	

				 DELETE ADS
				   FROM AccPeriodDutySummary ADS
				  INNER JOIN @TempAcctMissing TA ON ADS.APD_ScheduledPersonID = ta.ScheduledPersonID
								   and TA.weeknumber >= ADS.APD_AccPeriodStartWeek  and  TA.weeknumber <= ADS.APD_AccPeriodEndWeek


		INSERT INTO AccPeriodDutySummary (
					APD_ScheduledPersonID,
					APD_AccPeriodStartDate,
					APD_AccPeriodEndDate,
					APD_AccPeriodStartWeek,
					APD_AccPeriodEndWeek,
					APD_AccPeriodDuration,
					APD_AccPeriodDays,
					APD_TotalOverTimeHRS,
					APD_CreatedBy,
					APD_CreatedDate)
			 SELECT TA.ScheduledPersonID ,
					MIN(TD.dDateTime) startdate,
					MAX(TD.dDateTime) enddate,
					TA.weeknumber startweek,
					TA.weeknumber endweek,
					MAX(isnull(TA.totalduration,0)),
					MAX(isnull(TA.NoOfDays,0)),
					MAX(isnull(TA.OverTimeHrs,0)),
					1, 
				    getutcdate()
			  from @TempAcctMissing TA 
			  inner join TimeDimension TD on TA.weeknumber = TD.ixYearWeek
			 group by TA.ScheduledPersonID, TA.weeknumber 

     
        IF ( @@TRANCOUNT  > 0 ) 
         BEGIN
           COMMIT  TRANSACTION 
         END       

		RETURN 0          

        END TRY
        
        BEGIN CATCH
                 
        IF ( @@TRANCOUNT  > 0 ) 
         BEGIN
          ROLLBACK  TRANSACTION  
		 END

		 INSERT INTO ErrorLog
		        (ErrorNumber,
				 ErrorState,
				 ErrorSeverity,
				 ErrorProcedure,
				 ErrorLine,
				 ErrorMessage,
				 ErrorDateTime,
				 UserName
				)
         SELECT ERROR_NUMBER() AS ErrorNumber,
                ERROR_STATE() AS ErrorState,
				ERROR_SEVERITY() AS ErrorSeverity,
				ERROR_PROCEDURE() AS ErrorProcedure,
				ERROR_LINE() AS ErrorLine,
				ERROR_MESSAGE() AS ErrorMessage,
				getutcdate(),
				@vuserID		  

		 RETURN 1  		  
          
        END CATCH;
        
END