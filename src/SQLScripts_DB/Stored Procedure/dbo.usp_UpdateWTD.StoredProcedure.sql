USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_UpdateWTD]    Script Date: 04/11/2025 16:29:06 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                    PROCEDURE  [dbo].[usp_UpdateWTD]
@AllocationSPID    INT,
@pNetLogin       VARCHAR(30)

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
    DECLARE @pWeekNumber        INT = 0
	DECLARE @DutyDate           DATE
    DECLARE @pteamId            INT
    DECLARE @schedulingpersonid INT
	DECLARE @vname              VARCHAR(100)
	DECLARE @DutyName           NVARCHAR(100)
 	DECLARE @WTD_DutyName       NVARCHAR(100)
 	DECLARE @WTD_DutyDate       DATE
	DECLARE @vuserID            INT	
	DECLARE @LeaveDutyStartDate DATE
	DECLARE @LeaveDutyEndDate   DATE
	DECLARE @HDate              VARCHAR(100)
	DECLARE @LeaveChkStartdate  DATE
	DECLARE @LeaveChkEnddate    DATE
    DECLARE @workTimeDirectiveOptOut TINYINT 
    DECLARE @checkOverSixDaysWorked TINYINT	;
	
    DECLARE @TempAllocationsWTD TABLE ( schedulingpersonid INT,
										weeknumber INT,
										duration INT,
										starttime INT,
										endtime INT,
										dutydate DATE,
										iDay INT,
										DutyName NVARCHAR(100),
										ID INT,
										IsUnderElevenBreakOverride BIT,
										IsUnderElevenBreak BIT)	

    DECLARE @AllocationBreachDataTemp TABLE ( SchedulingTeamId INT,
        	                              SchedulingPersonID INT, 
										  breachtype INT,
										  startdate DATE,
										  enddate DATE, 
										  BreachedBy VARCHAR(100),
										  BreachedDate DATETIME,
										  IsApproved INT,
										  History VARCHAR(250))	

    DECLARE @AllocationBreachData TABLE ( SchedulingTeamId INT,
        	                              SchedulingPersonID INT, 
										  breachtype INT,
										  startdate DATE,
										  enddate DATE, 
										  BreachedBy VARCHAR(100),
										  BreachedDate DATETIME,
										  IsApproved INT,
										  History VARCHAR(250),
										  AllocationID INT,
										  TurnaroundTime INT)	
	
	BEGIN TRY

	 SET @HDate = FORMAT(Getdate(),'HH:mm') + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy')

	   SELECT @vuserID = UD_UserID,
			  @vname = UD_DisplayName
		 FROM UserDetails
		WHERE UD_NetLogin = @pNetLogin  		
 
	 SELECT @pWeekNumber = AL_WeekNumber, 
	        @pteamId = AL_SchedulingTeamID,
			@schedulingpersonid = ASP_SchedulingPersonID,
			@DutyDate = ASP_DutyDate,
			@DutyName = AD_DutyName
	   FROM Allocations AL
	  INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
	  INNER JOIN AllocationsDuties AD on ASP_AllocationsDutyID = AD_AllocationsDutyID 
	  WHERE ASP_AllocationsSPID = @AllocationSPID	 

	   SELECT @workTimeDirectiveOptOut=workTimeDirectiveOptOut ,
			  @checkOverSixDaysWorked=checkOverSixDaysWorked
		 FROM schedulingTeams  WITH(NOLOCK) 
		WHERE schedulingTeamId = @pteamId
  

	 INSERT INTO @TempAllocationsWTD (  schedulingpersonid,
										weeknumber,
										duration,
										starttime,
										endtime,
										dutydate,
										iDay,
										DutyName,
										ID,
										IsUnderElevenBreakOverride,
										IsUnderElevenBreak)		  
	 SELECT ASP_SchedulingPersonID,
	        AL_WeekNumber,
			AD_Duration,
			AD_StartTimeSec,
			AD_EndTimeSec,
			AD_DutyDate,
			AD_iDay,
			CASE WHEN AD_DutyName = 'Leave' and ASP_LeaveType = 1 THEN 'Leave'
			     WHEN AD_DutyName = 'Leave' and ASP_LeaveType <> 1 THEN 'OtherLeave'
				 ELSE AD_DutyName END,
			ASP_AllocationsSPID,
			CASE WHEN ASP_UnderElevenBreakStatus = 2 then 1 else 0 end as IsUnderElevenBreakOverride,
			CASE WHEN ASP_UnderElevenBreakStatus = 1 then 1 else 0 end as IsUnderElevenBreak
	   FROM Allocations AL
	  INNER JOIN TimeDimension TD ON AL_WeekNumber = TD.ixYearWeek 
	  INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID and ASP_iDay = TD.ixDayInWeek	   
	  INNER JOIN AllocationsDuties AD on ASP_AllocationsDutyID = AD_AllocationsDutyID 
	    WHERE AL_SchedulingTeamID = @pteamId
          AND ASP_SchedulingPersonID = @schedulingpersonid 
		  AND TD.dDateTime between DATEADD(DAY,-21,@DutyDate) AND DATEADD(DAY,21,@DutyDate )   

           SELECT @LeaveChkStartdate = MAX(Dutydate)
		    FROM
		   ( SELECT TD.dDateTime as Dutydate, TWTD.DutyName as DutyName
		       FROM TimeDimension TD
			   LEFT JOIN @TempAllocationsWTD TWTD ON TWTD.dutydate = TD.dDateTime
			  WHERE TD.dDateTime between DATEADD (DAY, -21, @DutyDate) and @DutyDate
			 ) FD WHERE DutyName IS NULL

           SELECT @LeaveChkEnddate = MIN(Dutydate)
		    FROM
		   ( SELECT TD.dDateTime as Dutydate, TWTD.DutyName as DutyName
		       FROM TimeDimension TD
			   LEFT JOIN @TempAllocationsWTD TWTD ON TWTD.dutydate = TD.dDateTime
			  WHERE TD.dDateTime between @DutyDate and DATEADD(DAY,21,@DutyDate)
			 ) FD WHERE DutyName IS NULL
			
		     
		   SET @LeaveChkStartdate = CASE WHEN @LeaveChkStartdate IS NULL 
		                                 THEN DATEADD (DAY,-21,@DutyDate)
										 ELSE DATEADD (DAY,1,@LeaveChkStartdate) END

		   SET @LeaveChkEnddate = CASE WHEN @LeaveChkEnddate IS NULL 
		                               THEN DATEADD(DAY,21,@DutyDate)
									   ELSE DATEADD (DAY,-1,@LeaveChkEnddate) END

	 INSERT INTO @AllocationBreachData  ( SchedulingTeamId,
										  SchedulingPersonID, 
										  breachtype,
										  startdate,
										  enddate, 
										  BreachedBy,
										  BreachedDate,
										  IsApproved,
										  History,
										  AllocationID,
										  TurnaroundTime )
     SELECT @pteamId as SchedulingTeamId,
	       fd.SchedulingPersonID as SchedulingPersonID, 
		   1 as breachtype,
	       cast(fd.startdate as date) as startdate,
		   cast(fd.enddate as date) as enddate, 
           @vname as BreachedBy,
           getdate() as BreachedDate,
		   CASE WHEN @workTimeDirectiveOptOut =1 THEN 1 ELSE 0 END AS IsApproved,
		   'WTD Type 1 Breached By '+@vname+' at '
						   + FORMAT(Getdate(),'HH:mm')
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') as History,
		   AllocationID as AllocationID,
		   TurnaroundTime as TurnaroundTime		   
	FROM (
	SELECT TD.SchedulingPersonID,
		   TD.DutyDate   as StartDate,
		   DATEADD(DAY,1,TD.DutyDate) as EndDate, 			
		   ( SELECT ( CASE WHEN TD.EndTime <= ET.StartTime 
		                    AND TD.EndTime < TD.StartTime THEN ET.StartTime - TD.EndTime
						   WHEN TD.EndTime > ET.StartTime then (86400-TD.EndTime ) + ET.StartTime end ) as TurnaroundTime
			   FROM @TempAllocationsWTD ET
			  WHERE ET.SchedulingPersonID = TD.SchedulingPersonID
				AND et.DutyDate = DATEADD(DAY,1,TD.DutyDate)
				AND NOT (ET.starttime=0 and ET.endtime=0)
				AND upper(ET.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
		   ) AS TurnaroundTime,
		   ( SELECT ET.ID as AllocationID
			   FROM @TempAllocationsWTD ET
			  WHERE ET.SchedulingPersonID = TD.SchedulingPersonID
				AND et.DutyDate = DATEADD(DAY,1,TD.DutyDate)
				AND NOT (ET.starttime=0 and ET.endtime=0)
				AND upper(ET.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
		   ) AS AllocationID	   
	  from @TempAllocationsWTD  TD
	  WHERE TD.DutyDate between DATEADD(DAY,-1, @DutyDate)  and @DutyDate
	    AND NOT (TD.starttime=0 and TD.endtime=0)
		AND upper(TD.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
			) FD where CAST(isnull(TurnaroundTime,54000) AS FLOAT)/CAST(3600 AS FLOAT) < 11		

    IF 	( SELECT COUNT(1) 
	        FROM @AllocationBreachData ) > 0
	  BEGIN	   

	    	UPDATE AL
	           SET AL.ASP_UnderElevenBreakStatus = 1,
			       AL.ASP_CalculatedUnderElevenHrs = ((11*3600) - WTD.TurnaroundTime ),
				   AL.ASP_OverrideUnderElevenHrs = 0,
				   AL.ASP_UnderElevenComments = NULL,
				   AL.ASP_UpdatedBy = @vuserID, 
				   AL.ASP_UpdatedDate = getutcdate()
	          FROM AllocationsScheduledPersons AL 
			  INNER JOIN @AllocationBreachData WTD ON WTD.AllocationID = AL.ASP_AllocationsSPID

	  END
	  
    IF EXISTS ( SELECT 1 
	              FROM @TempAllocationsWTD AL
				 WHERE AL.DutyDate between @DutyDate and DATEADD(DAY,1, @DutyDate)
				   AND IsUnderElevenBreak = 1
				   AND NOT EXISTS ( SELECT 1 
				                      FROM @AllocationBreachData WTD 
									 WHERE AL.ID = WTD.AllocationID ) )
	  BEGIN

			  UPDATE AL
	           SET AL.ASP_UnderElevenBreakStatus = 0,
			       AL.ASP_CalculatedUnderElevenHrs = 0,
				   AL.ASP_OverrideUnderElevenHrs = 0,
				   AL.ASP_UnderElevenComments = NULL,
				   AL.ASP_UpdatedBy = @vuserID, 
				   AL.ASP_UpdatedDate = getutcdate()
	          FROM AllocationsScheduledPersons AL
			  INNER JOIN (  SELECT TAL.ID
							  FROM @TempAllocationsWTD TAL
							 WHERE TAL.DutyDate between @DutyDate and DATEADD(DAY,1, @DutyDate)
							   AND IsUnderElevenBreak = 1
							   AND NOT EXISTS ( SELECT 1 
												  FROM @AllocationBreachData WTD 
												 WHERE TAL.ID = WTD.AllocationID ) ) FD ON FD.ID = AL.ASP_AllocationsSPID

	  END	  
	  
	INSERT INTO history ( historytype,
					  attributeid,
					  HistorySubType,
					  datetime,
					  userid,
					  history )		
	SELECT ht.id AS historytype,
		   AL.ID AS attributeid,
		   'PH' AS HistorySubType,
		   getdate(),
		   @vuserID,
		   'Under11 Override was removed by the system '
		   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
		   + FORMAT(Getdate(),'HH:mm')+'.'
		FROM @TempAllocationsWTD AL
	 INNER JOIN @AllocationBreachData WTD ON AL.ID=WTD.AllocationID 
	 INNER JOIN HistoryTypes HT ON 1=1
	 WHERE AL.IsUnderElevenBreakOverride = 1
	   AND HT.historytype = 'AllocationScheduledPerson'

	BEGIN
	
     DECLARE CUR_WTD CURSOR FOR 
        SELECT DutyName, DutyDate
		  FROM @TempAllocationsWTD
		  WHERE DutyName IN ('LEAVE','-')--,'OFF LEAVE')
		   AND dutydate between @LeaveChkStartdate and @LeaveChkEnddate
		 ORDER BY dutydate

     OPEN CUR_WTD

     FETCH NEXT FROM CUR_WTD INTO @WTD_DutyName, @WTD_DutyDate

      WHILE @@FETCH_STATUS = 0
	   BEGIN
	    IF ( @WTD_DutyName IN ('LEAVE','-'))--,'OFF LEAVE')  )
		 BEGIN
		   
		    SELECT @LeaveDutyEndDate = MIN(DutyDate) 
			  FROM @TempAllocationsWTD  
			 WHERE DutyDate > @WTD_DutyDate 
			   AND DutyDate < @LeaveChkEnddate
			   AND DutyName NOT IN ('LEAVE','-')--,'OFF LEAVE') 

		    SELECT @LeaveDutyStartDate = MAX(DutyDate) 
			  FROM @TempAllocationsWTD  
			 WHERE DutyDate <  @WTD_DutyDate
			   AND DutyDate >  @LeaveChkStartdate
			   AND DutyName NOT IN ('LEAVE','-')--,'OFF LEAVE') 

            IF ( @LeaveDutyEndDate IS NOT NULL AND @LeaveDutyStartDate IS NOT NULL)
			 BEGIN

				 INSERT INTO @AllocationBreachDataTemp  ( SchedulingTeamId,
													  SchedulingPersonID, 
													  breachtype,
													  startdate,
													  enddate, 
													  BreachedBy,
													  BreachedDate,
													  IsApproved,
													  History)
				 SELECT @pteamId as SchedulingTeamId,
					   fd.SchedulingPersonID as SchedulingPersonID, 
					   1 as breachtype,
					   cast(fd.startdate as date) as startdate,
					   cast(fd.enddate as date) as enddate, 
					   @vname as BreachedBy,
					   getdate() as BreachedDate,
					   CASE WHEN @workTimeDirectiveOptOut =1 THEN 1 ELSE 0 END AS  IsApproved,
					   'WTD Type 1 Breached By '+@vname+' at ' + @HDate as History
				FROM (
				SELECT TD.SchedulingPersonID,
					   TD.DutyDate         as StartDate,
					   @LeaveDutyEndDate   as EndDate, 			
					   ( SELECT ( CASE WHEN TD.EndTime <= ET.StartTime 
										AND TD.EndTime < TD.StartTime THEN ET.StartTime - TD.EndTime
									   WHEN TD.EndTime >= ET.StartTime 
										AND TD.EndTime < TD.StartTime 
										AND ET.starttime < ET.endtime THEN TD.EndTime - ET.StartTime
									   WHEN TD.EndTime > ET.StartTime then (86400-TD.EndTime ) + ET.StartTime end ) as TurnaroundTime
						   FROM @TempAllocationsWTD ET
						  WHERE ET.SchedulingPersonID = TD.SchedulingPersonID
							AND et.DutyDate = @LeaveDutyEndDate
							AND NOT (ET.starttime=0 and ET.endtime=0)
							AND upper(ET.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
					   ) AS TurnaroundTime,
					   0 AS AllocationID	   
				  from @TempAllocationsWTD  TD
				  WHERE TD.DutyDate = @LeaveDutyStartDate 
					AND NOT (TD.starttime=0 and TD.endtime=0)
					AND upper(TD.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
						) FD where CAST(isnull(TurnaroundTime,54000) AS FLOAT)/CAST(3600 AS FLOAT) < 11	
						
			 END
		 END

			SET @LeaveDutyStartDate = NULL
		    SET @LeaveDutyEndDate = NULL
			SET @WTD_DutyName = NULL
			SET @WTD_DutyDate = NULL

		 	FETCH NEXT FROM CUR_WTD INTO @WTD_DutyName, @WTD_DutyDate

	  END
	    CLOSE CUR_WTD;

        DEALLOCATE CUR_WTD;  
	 END 

	INSERT INTO @AllocationBreachData  ( SchedulingTeamId,
										 SchedulingPersonID, 
										 breachtype,
										 startdate,
										 enddate, 
										 BreachedBy,
										 BreachedDate,
										 IsApproved,
										 History)
	SELECT DISTINCT SchedulingTeamId,
					SchedulingPersonID, 
					breachtype,
					startdate,
					enddate, 
					BreachedBy,
					BreachedDate,
					IsApproved,
					History
			   FROM @AllocationBreachDataTemp
				
	 INSERT INTO @AllocationBreachData  ( SchedulingTeamId,
										  SchedulingPersonID, 
										  breachtype,
										  startdate,
										  enddate, 
										  BreachedBy,
										  BreachedDate,
										  IsApproved,
										  History,
										  AllocationID,
										  TurnaroundTime )			
	SELECT @pteamId as SchedulingTeamId,
	       fd.SchedulingPersonID as SchedulingPersonID, 
		   4 as breachtype,
	       fd.startdate as startdate,
		   fd.enddate as enddate, 
           @vname as BreachedBy,
           getdate() as BreachedDate,
		   	CASE WHEN @workTimeDirectiveOptOut =1 THEN 1  WHEN
                    @checkOverSixDaysWorked=1 THEN 0 WHEN  
					@checkOverSixDaysWorked =0 THEN 1
					ELSE 0 END AS  IsApproved,
		   'WTD Type 4 Breached By '+@vname+' at '
				           + FORMAT(Getdate(),'HH:mm')
						   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') as History,
           NULL as AllocationID,
		   NULL as TurnaroundTime
	  FROM (
		  SELECT ST.SchedulingPersonID, 
		         st.DutyDate as startdate,
				 DATEADD(DAY,6,st.DutyDate) AS EndDate,
				 (SELECT count(1) 
				    FROM @TempAllocationsWTD ET
				   WHERE ET.SchedulingPersonID = ST.SchedulingPersonID
				     AND et.DutyDate between st.DutyDate and  DATEADD(DAY,6,st.DutyDate) 
					 AND ET.DutyDate between DATEADD(DAY,-6, @DutyDate)  and DATEADD(DAY,6,@DutyDate  )
					 AND upper(ET.Dutyname) not in ('U-SICK','-SICK','ABSENT','U','OFF LEAVE')
					 AND ISNULL(ET.Duration,0) > 0
				  ) dutycount
		    FROM @TempAllocationsWTD ST
		   WHERE upper(ST.Dutyname) not in ('U-SICK','-SICK','ABSENT','U','OFF LEAVE')
		     AND ISNULL(ST.Duration,0) > 0
			 AND ST.DutyDate between DATEADD(DAY,-6, @DutyDate)  and DATEADD(DAY,6,@DutyDate  )
	      ) FD where DutyCount > 6	  
		  	
		INSERT INTO working_time_directive( 
			   SchedulingPersonID, BreachType, StartDate,EndDate,
			   BreachedBy,BreachedDate,IsApproved,Comments,History )
		SELECT SchedulingPersonID,breachtype,StartDate,
			   EndDate,BreachedBy,BreachedDate,IsApproved,
			   CASE WHEN IsApproved =1 THEN  'Automatically approved due to team settings'
			   ELSE NULL END ,			   
			   CASE WHEN IsApproved =1 THEN  history +' <br> Comment added due to auto approval at '+ FORMAT(Getdate(),'HH:mm')
						 + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy' )
			   ELSE History END
			   history
		 FROM @AllocationBreachData AD
		 WHERE NOT EXISTS (
						   SELECT 1 
							 FROM working_time_directive WD
							WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate)	;	

		WITH DBD (ID)
		AS
		(SELECT ID
		FROM working_time_directive WD
		where WD.SchedulingPersonID = @SchedulingPersonID
		  and WD.startdate >= DATEADD(DAY,-1,@dutydate)
		  AND WD.EndDate <= DATEADD(DAY,1,@dutydate)
		  AND DATEDIFF(DAY,WD.StartDate, WD.EndDate) = 1
		  AND WD.breachtype = 1
		  AND WD.SchedulingPersonID IN (SELECT DISTINCT schedulingpersonid FROM @TempAllocationsWTD )
		  and not exists ( SELECT 1 
							 FROM @AllocationBreachData AD
							WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate
							  AND DATEDIFF(DAY, AD.startdate ,AD.enddate) = 1 )
			)
			DELETE from DBD	;	

		WITH DBD (ID)
		AS
		(SELECT ID
		FROM working_time_directive WD
		where WD.SchedulingPersonID = @SchedulingPersonID
		  and WD.startdate >= DATEADD(DAY,-21,@dutydate)
		  AND WD.EndDate <= DATEADD(DAY,21,@dutydate)
		  AND DATEDIFF(DAY, WD.StartDate,WD.EndDate) > 1
		  AND WD.breachtype = 1
		  AND WD.SchedulingPersonID IN (SELECT DISTINCT schedulingpersonid FROM @TempAllocationsWTD )
		  and not exists ( SELECT 1 
							 FROM @AllocationBreachData AD
							WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate
							  AND DATEDIFF(DAY, AD.startdate,AD.enddate) > 1 )
			)
			DELETE from DBD	;	

		WITH WBD (ID)
		AS
		(SELECT ID
		FROM working_time_directive WD
		where WD.SchedulingPersonID = @SchedulingPersonID		
		  and WD.startdate >= DATEADD(DAY,-6,@dutydate)
		  AND WD.EndDate <= DATEADD(DAY,6,@dutydate)
		  AND WD.breachtype = 4
		  AND WD.SchedulingPersonID IN (SELECT DISTINCT schedulingpersonid FROM @TempAllocationsWTD )
		  and not exists ( SELECT 1 
							 FROM @AllocationBreachData AD
							WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
							  AND WD.breachtype = AD.breachtype
							  AND WD.Startdate = AD.Startdate
							  AND WD.enddate = AD.enddate)
			)
			DELETE from WBD;			
		
		RETURN 0
			
	END TRY
				
	BEGIN CATCH

	   IF ( ERROR_NUMBER() < 50000 )
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