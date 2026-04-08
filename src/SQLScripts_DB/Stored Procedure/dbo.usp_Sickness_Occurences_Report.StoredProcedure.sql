USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Sickness_Occurences_Report]    Script Date: 24/12/2025 22:39:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Chandan Singh
-- Create date: 22nd-Feb-2023
-- Description:	Sickness report details
-- =============================================
CREATE OR ALTER       PROCEDURE [dbo].[usp_Sickness_Occurences_Report]
	@TeamID INT,
	@sDate  DATE,
	@eDate  DATE,
	@SPID   INT = NULL
AS
BEGIN
	
	SET NOCOUNT ON;

	SET DATEFORMAT YMD;

	DECLARE @TempSickData TABLE ( schedulingpersonid INT,
								  dutydate           DATE,
								  Duration           INT,
								  IsOcc              INT)	

	DECLARE @TempSickDataOccurance TABLE ( schedulingpersonid INT,
										   MaxConsecDays INT,
										   ThreeOccIn12 INT,
										   FiveOccIn52 INT,
										   TwentyEightConsecDaysIn52 INT)	

   DECLARE @vSchedulingPersonID INT, 
           @vIsOcc INT,
		   @vPrevSchedulingPersonID INT = 0,
		   @vPrevIsOcc INT,
		   @OccCntr INT = 0,
		   @MaxOcc INT = 0;

  INSERT INTO @TempSickData
        ( schedulingpersonid,
		  dutydate,
		  Duration
	    )
  SELECT ASP.ASP_SchedulingPersonID AS ASP_SchedulingPersonID, 
         AD.AD_DutyDate AS DutyDate,
		 ASP.ASP_LeaveDuration AS Duration	
	from Allocations AL WITH(NOLOCK)
   INNER JOIN  AllocationsScheduledPersons ASP WITH (NOLOCK) ON AL.AL_AllocationsID=ASP.ASP_AllocationsID
   INNER JOIN  AllocationsDuties AD WITH (NOLOCK) ON  AD.AD_AllocationsDutyID=ASP.ASP_AllocationsDutyID
   INNER JOIN TimeDimension TD ON TD.ixYearWeek = al.AL_WeekNumber AND td.ixDayInWeek = AD.AD_iDay
   INNER JOIN ScheduledPersonTeam_LINK SL on SL.ScheduledPersonID = ASP.ASP_SchedulingPersonID 
                                         AND SL.TeamID = AL.AL_SchedulingTeamID
   INNER JOIN ScheduledPersonTeam_LINK SL1 on SL.ScheduledPersonID = SL1.ScheduledPersonID
   where td.dDateTime BETWEEN @sDate AND @eDate        
	 AND SL1.TeamID = @teamId
	 AND SL1.scheduledType = 1
	 AND SL1.IsHomeTeam = 1 
	 AND GETDATE() between SL1.StartDate and SL1.EndDate
	 AND SL.scheduledType = 1
	 AND AD.AD_DutyDate between SL.StartDate and SL.EndDate
	 AND SL.IsHomeTeam = 1
	 AND SL1.ScheduledPersonID = ISNULL(@SPID, SL1.ScheduledPersonID) 
	 AND ASP_LeaveType IN (3,4,5)
	 AND AD_DutyType = 8

	      UPDATE TD
		     SET TD.IsOcc = TS.IsOcc
			FROM @TempSickData TD
	       INNER JOIN (
						SELECT TS.SchedulingPersonID,
							   TS.DutyDate,
								CASE WHEN DATEDIFF(DAY,TD.DutyDate,TS.DutyDate) IS NULL 
									THEN 0 ELSE 1 END AS IsOcc
						FROM @TempSickData TS
						LEFT JOIN @TempSickData TD ON DATEADD(DAY,1,TD.DutyDate) = TS.DutyDate
							  AND TS.SchedulingPersonID = TD.SchedulingPersonID
				       ) TS ON TS.schedulingpersonid = TD.schedulingpersonid 
					       AND TS.dutydate = TD.dutydate

            INSERT INTO @TempSickDataOccurance( schedulingpersonid ,
										   MaxConsecDays ,
										   ThreeOccIn12 ,
										   FiveOccIn52 ,
										   TwentyEightConsecDaysIn52)			
			SELECT schedulingpersonid,
			       0,
				   1,
				   0,
				   0
			  FROM (
				  SELECT ST.SchedulingPersonID,						
						 (SELECT count(1) 
							FROM @TempSickData ET
						   WHERE ET.SchedulingPersonID = ST.SchedulingPersonID
							 AND et.DutyDate between st.DutyDate and DATEADD(WEEK,12,st.DutyDate )
							 AND ET.IsOcc = 0
						  ) OccCount
					FROM @TempSickData ST
				  ) FD where OccCount > 2	  

            INSERT INTO @TempSickDataOccurance( schedulingpersonid ,
										   MaxConsecDays ,
										   ThreeOccIn12 ,
										   FiveOccIn52 ,
										   TwentyEightConsecDaysIn52)			
			SELECT schedulingpersonid,
			       0,
				   0,
				   1,
				   0
			  FROM (
				  SELECT ST.SchedulingPersonID,						
						 (SELECT count(1) 
							FROM @TempSickData ET
						   WHERE ET.SchedulingPersonID = ST.SchedulingPersonID
							 AND et.DutyDate between st.DutyDate and DATEADD(YEAR,1,st.DutyDate )
							 AND ET.IsOcc = 0
						  ) OccCount
					FROM @TempSickData ST
				  ) FD where OccCount > 4	


              BEGIN

				DECLARE CUR_Occ CURSOR FOR
				SELECT SchedulingPersonID,
					    IsOcc
				FROM @TempSickData 
				ORDER BY SchedulingPersonID, DutyDate

				OPEN CUR_Occ

				FETCH NEXT FROM CUR_Occ INTO @vSchedulingPersonID, @vIsOcc

				WHILE @@FETCH_STATUS = 0
				 BEGIN

				   IF  (      @vPrevSchedulingPersonID <> 0 
				          AND @vPrevSchedulingPersonID <> @vSchedulingPersonID 
						)
				    BEGIN
				        INSERT INTO @TempSickDataOccurance( schedulingpersonid ,
								MaxConsecDays ,
								ThreeOccIn12 ,
								FiveOccIn52 ,
								TwentyEightConsecDaysIn52)	
						VALUES (@vPrevSchedulingPersonID,
								@MaxOcc,
								0,
								0,
								CASE WHEN @MaxOcc > 27 THEN 1 ELSE 0 END
								)
                        
						SET @OccCntr = 0
						SET @MaxOcc = 0

				    END

				    IF ( @vPrevSchedulingPersonID = @vSchedulingPersonID )
					 BEGIN

					   IF ( @vPrevIsOcc = 0 AND @vIsOcc = 1)
					     BEGIN  SET @OccCntr = 2  END

					   IF ( @vPrevIsOcc = 1 AND @vIsOcc = 1)
					     BEGIN  SET @OccCntr = @OccCntr + 1  END

					   	  IF ( @MaxOcc < @OccCntr)
						    BEGIN
							  SET @MaxOcc = @OccCntr
							END

					   IF ( @vPrevIsOcc = 1 AND @vIsOcc = 0)
					     BEGIN  
						   SET @OccCntr = 0  						 
						 END
					   IF ( @vPrevIsOcc = 0 AND @vIsOcc = 0)
					     BEGIN  SET @OccCntr = 0  END

					 END
				    
					SET @vPrevSchedulingPersonID = @vSchedulingPersonID
					SET @vPrevIsOcc = @vIsOcc

					FETCH NEXT FROM CUR_Occ INTO @vSchedulingPersonID, @vIsOcc

				 END

				CLOSE CUR_Occ;

				DEALLOCATE CUR_Occ;

				        INSERT INTO @TempSickDataOccurance( schedulingpersonid ,
								MaxConsecDays ,
								ThreeOccIn12 ,
								FiveOccIn52 ,
								TwentyEightConsecDaysIn52)	
						VALUES (@vPrevSchedulingPersonID,
								@MaxOcc,
								0,
								0,
								CASE WHEN @MaxOcc > 27 THEN 1 ELSE 0 END
								)

			END

  SELECT AL.SchedulingPersonID,
         SP.UD_DisplayName DisplayName,
		 COUNT(1)                        AS DaysUnavailable,
		 MAX(OC.Occurrences )            AS Occurrences,
		 CAST(CAST(COUNT(1) AS DECIMAL)/7 AS decimal(18, 2)) AS WeeksSick,
		 SUM(CAST( (CAST(Duration AS DECIMAL)/CAST(3600 AS DECIMAL)) AS decimal(18, 2))) AS TotalHoursSick,
		 Max(DutyDate)                   AS MaxDutyDate,
		 COUNT(1)                        AS TotalDaysSick,
		 SUM(Duration)                   AS TotalSicknessHours,
		 MAX(MaxConsecDays) MaxConsecDays ,
		 MAX(ThreeOccIn12) ThreeOccIn12,
		 MAX(FiveOccIn52) FiveOccIn52,
		 MAX(TwentyEightConsecDaysIn52) TwentyEightConsecDaysIn52 
	FROM @TempSickData AL
   INNER JOIN UserDetails SP ON SP.UD_UserID = AL.SchedulingPersonID
	LEFT JOIN ( SELECT SchedulingPersonID,
					   COUNT(1) as Occurrences
			      FROM @TempSickData
			     WHERE IsOcc = 0
			     GROUP BY SchedulingPersonID 
                ) OC ON OC.SchedulingPersonID = AL.SchedulingPersonID
	LEFT JOIN (
	            SELECT schedulingpersonid,
				       MAX(MaxConsecDays) MaxConsecDays,
					   MAX(ThreeOccIn12) ThreeOccIn12,
					   MAX(FiveOccIn52) FiveOccIn52,
					   MAX(TwentyEightConsecDaysIn52) TwentyEightConsecDaysIn52 
				  FROM @TempSickDataOccurance
				 GROUP BY schedulingpersonid
			   ) OCData ON OCData.schedulingpersonid = AL.schedulingpersonid
	GROUP BY AL.SchedulingPersonID,
	         SP.UD_DisplayName 

 return;

END