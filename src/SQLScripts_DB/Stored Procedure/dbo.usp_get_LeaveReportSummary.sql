USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_LeaveReportSummary]    Script Date: 25/08/2025 15:43:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE  [dbo].[usp_get_LeaveReportSummary]
	-- Add the parameters for the stored procedure here navi
	@Teams varchar(max),
    @ColumnListChar    varchar(max)    = NULL,
	@ColumnListNumeric varchar(max)    = NULL,
	@group1 varchar(255)        = NULL,
	@group2 varchar(255)        = NULL,
	@LeaveYearFrom  int,
	@leaveYearTO int,
	@chargeCodes varchar(max)   = NULL,
	@staffNumbers varchar(max)  = NULL,
	@filterColName varchar(255) = NULL,
	@filterSign varchar(5)      = NULL,
	@filtervalue int            = NULL,
	@Orderby varchar(100)       = NULL,
	@Orderbyvlue varchar(15)    = NULL
	
AS
BEGIN

	-- SET NOCOUNT ON added to prevent extra result sets from
    SET NOCOUNT ON;
	DECLARE @SQL1	       VARCHAR(MAX);
	DECLARE @SQL2	       VARCHAR(MAX);	
	DECLARE @SQLAllocated  VARCHAR(MAX) = '';	
	DECLARE @SQLTaken      VARCHAR(MAX) = '';	
	DECLARE @SQLRemaining  VARCHAR(MAX) = '';	
	DECLARE @SQLAllocatedTotal  VARCHAR(MAX) = '';	
	DECLARE @SQLTakenTotal      VARCHAR(MAX) = '';
	DECLARE @SQLRemainingTotal  VARCHAR(MAX) = '';
	DECLARE @WhereFlag     BIT = 0;
	DECLARE @ColumName     VARCHAR(100);
	DECLARE @AddFlag       INT = 0;

	DECLARE @leaveyearstartdate date,@leaveyearenddate date
	set @leaveyearstartdate = DATEFROMPARTS(@LeaveYearFrom,04,01)
	set @leaveyearenddate = DATEFROMPARTS(@leaveYearTO+1,03,31)
	
	SET @SQL1 =   'SELECT  ScheduledPersonID,  
						   StaffNumber, 
						   DisplayName,
						   iyear,
						   schedulingTeamName,
						   Charge_Codes,
						   Annual,
						   comp,
						   PHL,
						   Additional,
						   Exceptional,
						   Under11TOIL,
						   Over12TOIL,
						   Casual,
						   LongService,
						   Other, 
						   ISNULL(Annual,0)+ISNULL(comp,0)+ISNULL(PHL,0)+ISNULL(Additional,0)+
						   ISNULL(Exceptional,0)+ ISNULL(Under11TOIL,0)+ ISNULL(Over12TOIL,0)+ ISNULL(Casual,0)+
						   ISNULL(LongService,0)+ ISNULL(Other,0) AS TotalLeaveAllocated,
						   AnnualTaken,
						   PHLTaken,
						   CompTaken,
						   AdditionalTaken,
						   ExceptionalTaken,
						   Under11TOILTaken,	
						   Over12TOILTaken,
						   CasualTaken,
						   LongServiceTaken,
						   OtherTaken,
						   ISNULL(AnnualTaken,0) + ISNULL( PHLTaken,0) + ISNULL( CompTaken,0) + ISNULL( AdditionalTaken,0) + 
						   ISNULL(ExceptionalTaken,0) + ISNULL( Under11TOILTaken,0) + ISNULL(	Over12TOILTaken,0) + 
						   ISNULL(CasualTaken,0) + ISNULL( LongServiceTaken,0) + ISNULL( OtherTaken,0) AS TotalLeaveTaken,
						   ISNULL(Annual,0) - ISNULL(AnnualTaken,0) AS AnnualRemaining,
						   ISNULL(PHL,0) - ISNULL(PHLTaken,0) AS PHLRemaining,
						   ISNULL(comp,0) - ISNULL(CompTaken,0) AS compRemaining,
						   ISNULL(Additional,0) - ISNULL(AdditionalTaken,0) AdditionalRemaining,
						   ISNULL(Exceptional,0) - ISNULL(ExceptionalTaken,0) AS ExceptionalRemaining,
						   ISNULL(Under11TOIL,0) - ISNULL(Under11TOILTaken,0) AS Under11TOILRemaining,
						   ISNULL(Over12TOIL,0) - ISNULL(Over12TOILTaken,0) AS Over12TOILRemaining,
						   ISNULL(Casual,0) - ISNULL(CasualTaken,0) AS CasualRemaining,
						   ISNULL(LongService,0) - ISNULL(LongServiceTaken,0) AS LongServiceRemaining,
						   ISNULL(Other,0) - ISNULL(OtherTaken,0) OtherRemaining,
						   (ISNULL(Annual,0)+ISNULL(comp,0)+ISNULL(PHL,0)+ISNULL(Additional,0)+
						   ISNULL(Exceptional,0)+ ISNULL(Under11TOIL,0)+ ISNULL(Over12TOIL,0)+ ISNULL(Casual,0)+
						   ISNULL(LongService,0)+ ISNULL(Other,0) ) -
						   (ISNULL(AnnualTaken,0) + ISNULL( PHLTaken,0) + ISNULL( CompTaken,0) + ISNULL( AdditionalTaken,0) + 
						   ISNULL(ExceptionalTaken,0) + ISNULL( Under11TOILTaken,0) + ISNULL(	Over12TOILTaken,0) + 
						   ISNULL(CasualTaken,0) + ISNULL( LongServiceTaken,0) + ISNULL( OtherTaken,0) ) AS TotalLeaveRemaining						    
				      FROM
	               (SELECT LA.Annual AS Annual,
						   LA.Comp AS comp,
						   LA.PHL AS PHL,
						   LA.Additional AS Additional,
						   LA.Exceptional AS Exceptional,
						   LA.Under11TOIL AS Under11TOIL,
						   LA.Over12TOIL AS Over12TOIL,
						   LA.Casual AS Casual,
						   LA.LongService AS LongService,
						   LA.Other AS Other, 
						   ISNULL(LAP.AnnualTaken,0) AS AnnualTaken,
						   ISNULL(LAP.PHLTaken,0) AS PHLTaken,
						   ISNULL(LAP.CompTaken,0) AS CompTaken,
						   ISNULL(LAP.AdditionalTaken,0) AS AdditionalTaken,
						   ISNULL(LAP.ExceptionalTaken,0) AS ExceptionalTaken,
						   ISNULL(LAP.Under11TOILTaken,0) AS Under11TOILTaken,	
						   ISNULL(LAP.Over12TOILTaken,0) AS Over12TOILTaken,
						   ISNULL(LAP.CasualTaken,0) AS CasualTaken,
						   ISNULL(LAP.LongServiceTaken,0) AS LongServiceTaken,
						   ISNULL(LAP.OtherTaken,0) AS OtherTaken,
						   LA.ScheduledPersonID,  
						   LA.StaffNumber, 
						   LA.DisplayName AS DisplayName,
						   LA.iyear,
						   LA.schedulingTeamName,
						   LA.Charge_Codes AS Charge_Codes
					FROM 
					( SELECT SUM(LA.Annual) AS Annual,
						   SUM(LA.Comp) AS comp,
						   SUM(LA.PHL) AS PHL,
						   SUM(LA.Additional) AS Additional,
						   SUM(LA.Exceptional) AS Exceptional,
						   SUM(LA.Under11TOIL) AS Under11TOIL,
						   SUM(LA.Over12TOIL) AS Over12TOIL,
						   SUM(LA.Casual) AS Casual,
						   SUM(LA.LongService) AS LongService,
						   SUM(LA.Other) AS Other,
						   SP.UD_UserID ScheduledPersonID,  
						   UD_StaffNumber StaffNumber, 
						   UD_DisplayName AS DisplayName,
						   iyear,
						   schedulingTeamName,
						   UC_CostCode Charge_Codes,
						   stl.TeamID
					FROM Leaveallocation as LA
					INNER JOIN UserDetails AS sp ON sp.UD_UserID = LA.SchedulingPersonID
					INNER JOIN ( select DISTINCT TeamID,ScheduledPersonID  FROM ScheduledPersonTeam_LINK WHERE TeamID IN ('+@Teams+') AND IsHomeTeam=1 
					AND ISNULL(convert(datetime,EndDate,110),''9999-01-01'') >= cast('''+cast(@leaveyearstartdate as varchar)+''' as DATE) 
					AND  convert(datetime,StartDate,110) <= cast('''+cast(@leaveyearenddate as varchar)
				   +''' as DATE))  AS stl  ON stl.ScheduledPersonID = sp.UD_UserID
					INNER JOIN Schedulingteams ST ON ST.schedulingTeamId = STL.TeamID
					LEFT JOIN UserConfigs as scp (nolock) on scp.UC_UserID = UD_UserID AND cast(getdate() AS DATE) BETWEEN scp.UC_StartDate AND scp.UC_EndDate
					WHERE LA.SchedulingPersonID > 0
					  AND LA.IsActive = 1 
					  AND LA.iYear BETWEEN '+CAST(@LeaveYEarFrom AS VARCHAR)+' AND '+CAST(@leaveYearTO  AS VARCHAR)
				 +' GROUP BY SP.UD_UserID,  
						     UD_StaffNumber, 
						     UD_DisplayName,
						     iyear,
						     schedulingTeamName,
						     UC_CostCode,
						     stl.TeamID
					) LA
					LEFT JOIN (
					SELECT SUM(CASE WHEN LT.AllocName=''Annual'' THEN RA.Amount ELSE 0 END) AS AnnualTaken,
						   SUM(CASE WHEN LT.AllocName=''PHL'' THEN RA.Amount ELSE 0 END) AS PHLTaken,
						   SUM(CASE WHEN LT.AllocName=''Comp'' THEN RA.Amount ELSE 0 END) AS CompTaken,
						   SUM(CASE WHEN LT.AllocName=''Additional'' THEN RA.Amount ELSE 0 END) AS AdditionalTaken,
						   SUM(CASE WHEN LT.AllocName=''Exceptional'' THEN RA.Amount ELSE 0 END) AS ExceptionalTaken,
						   SUM(CASE WHEN LT.AllocName=''Under11TOIL'' THEN RA.Amount ELSE 0 END) AS Under11TOILTaken,	
						   SUM(CASE WHEN LT.AllocName=''Over12TOIL'' THEN RA.Amount ELSE 0 END) AS Over12TOILTaken,
						   SUM(CASE WHEN LT.AllocName=''Casual'' THEN RA.Amount ELSE 0 END) AS CasualTaken,
						   SUM(CASE WHEN LT.AllocName=''LongService'' THEN RA.Amount ELSE 0 END) AS LongServiceTaken,
						   SUM(CASE WHEN LT.AllocName=''Other'' THEN RA.Amount ELSE 0 END) AS OtherTaken,
						   UD_USerID ScheduledPersonID,
						   CASE WHEN MONTH(LAP.ddate) <=3 THEN YEAR(LAP.ddate)-1 ELSE YEAR(LAP.ddate) END AS iyear,
						   stl.TeamID
					 FROM  LeaveApplications LAP 
					 INNER JOIN UserDetails AS sp ON LAP.SchedulingPersonID=UD_UserID 					      
			         INNER JOIN ref_LeaveApplications_Amounts RA ON RA.ApplicationID = LAP.ID
				     INNER JOIN LeaveAllocateTypes LT ON LT.ID=RA.LeaveTypeID
					 INNER JOIN ( select DISTINCT TeamID,ScheduledPersonID  FROM ScheduledPersonTeam_LINK WHERE TeamID IN ('+@Teams+') AND IsHomeTeam=1 
					AND ISNULL(convert(datetime,EndDate,110),''9999-01-01'') >= cast('''+cast(@leaveyearstartdate as varchar)+''' as DATE) 
					AND  convert(datetime,StartDate,110) <= cast('''+cast(@leaveyearenddate as varchar)
				   +''' as DATE))  AS stl  ON stl.ScheduledPersonID = SP.UD_UserID
					WHERE LAP.SchedulingPersonID > 0
					  AND LAP.ddate BETWEEN cast('''+cast(@leaveyearstartdate as varchar)+''' as DATE) AND cast('''+cast(@leaveyearenddate as varchar)
				   +''' as DATE) AND stl.TeamID IN ('+@Teams+')
					  AND LAP.Deleted = 0 
				    GROUP BY CASE WHEN MONTH(LAP.ddate) <=3 THEN YEAR(LAP.ddate)-1 ELSE YEAR(LAP.ddate) END, 
					         SP.UD_UserID, 
							 stl.TeamID
				   ) LAP ON LA.ScheduledPersonID = LAP.ScheduledPersonID 
				        AND LA.iyear = LAP.iyear 
						AND LA.TeamID = LAP.TeamID
				 ) IMD '

	IF ( @group1 IS NULL AND @group2 IS NULL AND @ColumnListNumeric IS NULL )
	 BEGIN	 
	    SET @SQL2 = ' SELECT * FROM ( '+@SQL1+') FD '	 
	 END
	
	IF ( @group1 IS NULL AND @group2 IS NULL AND @ColumnListNumeric IS NOT NULL )
	 BEGIN	 
		   SET @SQL2 = 'SELECT '+@ColumnListChar+' ,' 
		   DECLARE CUR_ColList CURSOR FOR 
		   select value FROM string_split(@ColumnListNumeric,',') 
		   OPEN CUR_ColList
		   FETCH NEXT FROM CUR_ColList INTO @ColumName					
	        WHILE @@FETCH_STATUS = 0
			 BEGIN
			   --
				SET @SQLAllocated = @SQLAllocated + CASE WHEN @AddFlag = 1 
				         THEN ' ,ISNULL('+@ColumName+',0) as '+@ColumName ELSE 'ISNULL('+@ColumName+',0) as '+@ColumName END
				SET @SQLAllocatedTotal = @SQLAllocatedTotal + CASE WHEN @AddFlag = 1 
				         THEN ' +ISNULL('+@ColumName+',0) ' ELSE 'ISNULL('+@ColumName+',0)' END
				SET @SQLTaken = @SQLTaken + ' ,ISNULL('+@ColumName+'Taken,0) as '+@ColumName+'Taken' 
				SET @SQLTakenTotal = @SQLTakenTotal + CASE WHEN @AddFlag = 1 
				         THEN ' +ISNULL('+@ColumName+'Taken,0) ' ELSE 'ISNULL('+@ColumName+'Taken,0) ' END
				SET @SQLRemaining = @SQLRemaining + ' ,ISNULL('+@ColumName+',0) - ISNULL('+@ColumName+'Taken,0) as '+@ColumName+'Remaining' 						
				SET @AddFlag = 1
			   --
			   FETCH NEXT FROM CUR_ColList INTO @ColumName
			  END
			 CLOSE CUR_ColList;
			 DEALLOCATE CUR_ColList; 
			-- SET @SQL2 = substring(@SQL2,1,DATALENGTH(@SQL2)- CHARINDEX(',',REVERSE(@SQL2)))  
			-- Start adding Total columns
					 IF ( @AddFlag = 1 ) 
					  BEGIN
						SET @SQLRemainingTotal = ' ( '+  @SQLAllocatedTotal +') - ( '+@SQLTakenTotal+' ) as TotalLeaveRemaining '
					    SET @SQLAllocatedTotal = @SQLAllocatedTotal + ' as TotalLeaveAllocated , '
						SET @SQLTakenTotal     = @SQLTakenTotal + ' as TotalLeaveTaken,'
					  END	
			 --

			 SET @SQL2 = @SQL2 + @SQLAllocated + @SQLTaken + @SQLRemaining + ','+@SQLTakenTotal 
			            + @SQLAllocatedTotal + @SQLRemainingTotal +' FROM ( '+@SQL1+') FD '

	 END
	 
	IF ( @group1 IS NOT NULL OR @group2 IS NOT NULL )
	 BEGIN	 
	    SET @SQL2 = ' SELECT '+ CASE WHEN @group1 IS NOT NULL AND @group2 IS NOT NULL THEN @group1+','+@group2
		                             WHEN @group1 IS NOT NULL AND @group2 IS NULL THEN @group1 		                             
									 WHEN @group1 IS NULL AND @group2 IS NOT NULL THEN @group2 END
	   IF ( @ColumnListNumeric IS NOT NULL)
	     BEGIN
		   SET @SQL2 = @SQL2+' ,' 
		   DECLARE CUR_ColList CURSOR FOR 
		   select value FROM string_split(@ColumnListNumeric,',') 
		   OPEN CUR_ColList
		   FETCH NEXT FROM CUR_ColList INTO @ColumName					
	        WHILE @@FETCH_STATUS = 0
			 BEGIN
			   --
				SET @SQLAllocated = @SQLAllocated + CASE WHEN @AddFlag = 1 
				         THEN ' ,SUM(ISNULL('+@ColumName+',0)) as '+@ColumName ELSE 'SUM(ISNULL('+@ColumName+',0)) as '+@ColumName END
				SET @SQLAllocatedTotal = @SQLAllocatedTotal + CASE WHEN @AddFlag = 1 
				         THEN ' +SUM(ISNULL('+@ColumName+',0)) ' ELSE 'SUM(ISNULL('+@ColumName+',0))' END
				SET @SQLTaken = @SQLTaken + ' ,SUM(ISNULL('+@ColumName+'Taken,0)) as '+@ColumName+'Taken' 
				SET @SQLTakenTotal = @SQLTakenTotal + CASE WHEN @AddFlag = 1 
				         THEN ' +SUM(ISNULL('+@ColumName+'Taken,0)) ' ELSE 'SUM(ISNULL('+@ColumName+'Taken,0)) ' END
				SET @SQLRemaining = @SQLRemaining + ' ,SUM(ISNULL('+@ColumName+',0)) - SUM(ISNULL('+@ColumName+'Taken,0)) as '+@ColumName+'Remaining' 						
				SET @AddFlag = 1

			   --
			   FETCH NEXT FROM CUR_ColList INTO @ColumName
			  END
			 CLOSE CUR_ColList;
			 DEALLOCATE CUR_ColList; 
			-- SET @SQL2 = substring(@SQL2,1,DATALENGTH(@SQL2)- CHARINDEX(',',REVERSE(@SQL2)))  
			-- Start adding Total columns
					 IF ( @AddFlag = 1 ) 
					  BEGIN
						SET @SQLRemainingTotal = ' ( '+  @SQLAllocatedTotal +') - ( '+@SQLTakenTotal+' ) as TotalLeaveRemaining '
					    SET @SQLAllocatedTotal = @SQLAllocatedTotal + ' as TotalLeaveAllocated , '
						SET @SQLTakenTotal     = @SQLTakenTotal + ' as TotalLeaveTaken,'
					  END	
			 --

			 SET @SQL2 = @SQL2 + @SQLAllocated + @SQLTaken + @SQLRemaining + ','+@SQLTakenTotal 
			            + @SQLAllocatedTotal + @SQLRemainingTotal +' FROM ( '+@SQL1+') FD '

		 END	
	   IF ( @ColumnListNumeric IS NULL)
	     BEGIN
		   SET @SQL2 = @SQL2+' ,SUM(Annual) AS Annual,SUM(Comp) as Comp,SUM(PHL) as PHL,SUM(Additional) as Additional,
		        SUM(Exceptional) as Exceptional,SUM(Under11TOIL) as Under11TOIL,SUM(Over12TOIL) as Over12TOIL,
				SUM(Casual) as Casual,SUM(LongService) as LongService,SUM(Other) as Other,
				SUM(AnnualTaken) AS AnnualTaken,SUM(CompTaken) as CompTaken,SUM(PHLTaken) as PHLTaken,
				SUM(AdditionalTaken) as AdditionalTaken,SUM(ExceptionalTaken) as ExceptionalTaken,
				SUM(Under11TOILTaken) as Under11TOILTaken,SUM(Over12TOILTaken) as Over12TOILTaken,
				SUM(CasualTaken) as CasualTaken,SUM(LongServiceTaken) as LongServiceTaken,SUM(OtherTaken) as OtherTaken,
				SUM(AnnualRemaining) AS AnnualRemaining,SUM(CompRemaining) as CompRemaining,
				SUM(PHLRemaining) as PHLRemaining,SUM(AdditionalRemaining) as AdditionalRemaining,
		        SUM(ExceptionalRemaining) as ExceptionalRemaining,SUM(Under11TOILRemaining) as Under11TOILRemaining,
				SUM(Over12TOILRemaining) as Over12TOILRemaining,
				SUM(CasualRemaining) as CasualRemaining,SUM(LongServiceRemaining) as LongServiceRemaining,
				SUM(OtherRemaining) as OtherRemaining,
				( SUM(Annual) + SUM(Comp) + SUM(PHL)+ SUM(Additional)+
		        SUM(Exceptional) + SUM(Under11TOIL) + SUM(Over12TOIL) +
				SUM(Casual)+SUM(LongService)+SUM(Other) ) as  TotalLeaveAllocated,
				( SUM(AnnualTaken) + SUM(CompTaken)+ SUM(PHLTaken) +
				SUM(AdditionalTaken) + SUM(ExceptionalTaken)+
				SUM(Under11TOILTaken) + SUM(Over12TOILTaken)+
				SUM(CasualTaken) + SUM(LongServiceTaken) + SUM(OtherTaken)  )  as TotalLeaveTaken,
				(SUM(AnnualRemaining) + SUM(CompRemaining)+
				SUM(PHLRemaining)+ SUM(AdditionalRemaining)+
		        SUM(ExceptionalRemaining) + SUM(Under11TOILRemaining)+
				SUM(Over12TOILRemaining)+ SUM(CasualRemaining) + SUM(LongServiceRemaining) +
				SUM(OtherRemaining) ) as TotalLeaveRemaining '
		        + ' FROM ( '+@SQL1+') FD '	
		 END		 
	 END	 
	
	IF ( @chargeCodes IS NOT NULL )
	  BEGIN
		SET @SQL2 = @SQL2+' WHERE Charge_Codes IN ( '''+REPLACE(@chargeCodes,',',''',''') +''' ) ';
		SET @WhereFlag = 1
	  END


	IF ( @staffNumbers IS NOT NULL AND @chargeCodes IS NOT NULL )
	  BEGIN
		SET @SQL2 = @SQL2+' AND StaffNumber IN ( '''+REPLACE(@staffNumbers,',',''',''')+''' ) ';
	  END
	IF ( @staffNumbers IS NOT NULL AND @chargeCodes IS NULL )
	  BEGIN
		SET @SQL2 = @SQL2+' WHERE StaffNumber IN ( '''+REPLACE(@staffNumbers,',',''',''')+''' ) ';
		SET @WhereFlag = 1
	  END	  
	  
	-- Group BY START--
	IF ( @group1 IS NOT NULL ) 
	  BEGIN 
		SET @SQL2 = @SQL2+' GROUP BY '+@group1
	  END	  
	IF ( @group2 IS NOT NULL AND @group1 IS NOT NULL ) 
	  BEGIN 
		SET @SQL2 = @SQL2+','+@group2
	  END	  
	IF ( @group2 IS NOT NULL AND @group1 IS NULL ) 
	  BEGIN 
		SET @SQL2 = @SQL2+' GROUP BY '+@group2
	  END

	IF(@Orderby IS NOT NULL AND @Orderbyvlue IS NOT NULL AND @filterColName IS NULL )
	 BEGIN  
		SET @SQL2 = @SQL2+' ORDER BY '+ @Orderby + ' ' + @Orderbyvlue + '' 
	 END
	----GROUP BY END----

	--Filter Setting START--
	IF(@filterColName IS NOT NULL ) 
	  BEGIN 
		SET @SQL2 = 'SELECT * FROM ( '+@SQL2+' ) FLT WHERE '+@filterColName + ' '+@filterSign +' '+ CAST(@filtervalue AS VARCHAR) 

			IF(@Orderby IS NOT NULL AND @Orderbyvlue IS NOT NULL )
			 BEGIN  
				SET @SQL2 = @SQL2+' ORDER BY '+ @Orderby + ' ' + @Orderbyvlue + '' 
			 END

	  END	 		  
	EXEC (@SQL2);	

   
END