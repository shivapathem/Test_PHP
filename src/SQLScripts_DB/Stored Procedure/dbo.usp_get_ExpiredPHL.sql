USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ExpiredPHL]    Script Date: 27/07/2025 20:15:13 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE  [dbo].[usp_get_ExpiredPHL]
	-- Add the parameters for the stored procedure here navi
	@PHLStartDate varchar(50),
	@numberofweeks int,
	@Teamid  int
	
AS
BEGIN

	-- SET NOCOUNT ON added to prevent extra result sets from
    SET NOCOUNT ON;
	Declare @PHLENDDate date;
	Declare @endweeknumber int;
	Declare @sqlVar nvarchar(max);

	IF ( @numberofweeks > 0 )
	 BEGIN
		SET @sqlVar = 'SELECT TOP 1 @endweeknumber = ixYearWeek FROM (SELECT DISTINCT TOP '+cast(@numberofweeks as nvarchar)+'  ixYearWeek 
						 FROM TimeDimension (nolock) WHERE ixYearWeek > (SELECT ixYearWeek  
						 FROM TimeDimension (nolock)
						 WHERE dDateTime = cast(getDate() as date)) ORDER BY ixYearWeek) as weeklist order BY ixYearWeek DESC'

		 exec  sp_executesql  @sqlVar , N'@endweeknumber int out',  @endweeknumber out

		 SELECT @PHLENDDate=max(dDateTime)  
		   FROM TimeDimension (nolock) 
		  WHERE ixYearWeek=@endweeknumber
	  END

	IF ( @numberofweeks = 0 )
	 BEGIN	
	   SET @PHLENDDate = cast(getDate()-1 as date)
	 END

		SELECT crd.userDisplayName,
			   crd.SchedulingPersonID,
			   crd.StaffNumber,
			   crd.PHLDATE,
			   crd.ExpDate,
			   crd.TimeDemensionID,
			   crd.ID,
			   crd.Comments,
			   debit_t.DebitDate,
			   crd.PHLAmount,
			   crd.rolling_sum,
			   debit_t.Amount,
			   debit_t.rolling_d_sum,
			   Isnull(debit_t.rolling_d_sum, 0) - crd.rolling_sum AS diff,
			   crd.iYear,@PHLENDDate as PHLENDDate
		FROM   (SELECT la.dDate                      AS PHLDATE,
					   la.SchedulingPersonID,
					   la.PHL                        AS PHLAmount,
					   la.iYear,
					   (SELECT Sum(PHL)
						FROM   Leaveallocation la1
						WHERE  la1.SchedulingPersonID = la.SchedulingPersonID
							   AND la1.dDate >= CONVERT(DATE, @PHLStartDate, 102)
							   AND la1.dDate <= la.dDate
							   AND la1.IsActive = 1) AS rolling_sum,
					   la.Comments,
					   la.TimeDemensionID,
					   la.ID,
					   Dateadd(year, 1, la.dDate)    AS ExpDate,
					   UD_DisplayName                AS userDisplayName,
					   UD_NetLogin NetLogin,
					   UD_StaffNumber StaffNumber
				FROM   Leaveallocation AS la
					   INNER JOIN UserDetails
							   ON UD_UserID = la.SchedulingPersonID
				WHERE  la.PHL IS NOT NULL
					   AND la.SchedulingPersonID > 0
					   AND la.SchedulingPersonID IN (SELECT UD_UserID FROM
						   UserDetails (nolock)
				INNER JOIN Scheduledpersonteam_link  AS stl ( nolock)  ON stl.ScheduledPersonID = UD_UserID
													 WHERE  stl.TeamID = @Teamid
															AND stl.IsHomeTeam = 1
															 AND  isnull(convert(datetime,stl.EndDate,110),cast(getdate() AS DATE)) >= cast(getdate() AS DATE)
						and cast (stl.StartDate AS DATE) <= cast (getdate() AS DATE)
															)
					   AND la.TimeDemensionID IS NOT NULL
					   AND la.is_PHL = 1
					   AND la.dDate <= @PHLENDDate 
					   AND la.dDate >= CONVERT(DATE, @PHLStartDate, 102)
					   AND la.IsActive = 1) crd
			   LEFT JOIN (SELECT ref.Amount,
								 (SELECT Sum(ref1.Amount)
								  FROM   Ref_leaveapplications_amounts AS ref1 (nolock)
										 INNER JOIN Leaveapplications AS la1
												 ON la1.ID = ref1.ApplicationID
								  WHERE  la1.dDate >= CONVERT(DATE, @PHLStartDate, 102)
										 AND la1.dDate <= la.dDate
										 AND la1.SchedulingPersonID = la.SchedulingPersonID
										 AND ref1.LeaveTypeID = 2
										 AND ref1.Amount > 0) AS rolling_d_sum,
								 la.dDate                     AS DebitDate,
								 la.SchedulingPersonID,
								 la.Login,
								 UD_DisplayName               AS userDisplayName,
								 UD_NetLogin NetLogin,
								 UD_StaffNumber StaffNumber
						  FROM   Ref_leaveapplications_amounts AS ref (nolock)
								 INNER JOIN Leaveapplications AS la (nolock) ON la.ID = ref.ApplicationID
								 INNER JOIN UserDetails (nolock) ON UD_UserID = la.SchedulingPersonID
						  WHERE  ref.LeaveTypeID = 2--PHL Type
								 AND la.SchedulingPersonID > 0
								 AND ref.Amount > 0
								 AND la.Approved = 1
								 AND la.dDate >= CONVERT(DATE, @PHLStartDate, 102)
								 AND la.dDate <= @PHLENDDate)
								 debit_t
					  ON debit_t.SchedulingPersonID = crd.SchedulingPersonID
						 AND debit_t .DebitDate BETWEEN crd.PHLDATE AND crd.ExpDate WHERE crd.ExpDate <= @PHLENDDate
		ORDER  BY crd.PHLDATE 
    
END